<?php

namespace App\Service;

use App\Dto\WorkTimeSummaryInputDto;
use App\Dto\WorkTimeSummaryOutputDto;
use App\Repository\EmployeeRepository;
use App\Repository\WorkTimeRepository;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Uuid;

class WorkTimeSummaryService{
    private float $monthlyNormMinutes;
    private float $standardRatePln;
    private float $overtimeRateMultiplier;

    public function __construct(
        private readonly WorkTimeRepository $workTimeRepository,
        private readonly EmployeeRepository $employeeRepository,
        ParameterBagInterface $params
    ) {
        $this->monthlyNormMinutes = (int) $params->get('work_time.monthly_norm_hours') * 60;
        $this->standardRatePln = (float) $params->get('work_time.standard_rate_pln');
        $this->overtimeRateMultiplier = (float) $params->get('work_time.overtime_rate_multiplier');
    }

    public function calculateSummary(WorkTimeSummaryInputDto $inputDto): WorkTimeSummaryOutputDto
    {
        $employee = $this->employeeRepository->find(Uuid::fromString($inputDto->employeeId));
        if (!$employee) {
            throw new NotFoundHttpException("Pracownik o ID " .$inputDto->employeeId." nie istnieje.");
        }

        $dateParts = explode('-', $inputDto->dateString);
        $month = $dateParts[0];
        $year = $dateParts[1];


        $startDateString = sprintf('%s-%s-01', $year, $month);
        $startDate = DateTimeImmutable::createFromFormat('Y-m-d', $startDateString);

        if ($startDate === false || $startDate->format('Y-m') !== sprintf('%s-%s', $year, $month)) {
            throw new BadRequestHttpException("Nieprawidłowa data: " .$inputDto->dateString);
        }

        $startDate = $startDate->setTime(0, 0, 0);
        $endDate = $startDate->modify('last day of this month')->setTime(23, 59, 59);


        $workTimes = $this->workTimeRepository->findByEmployeeAndDateRange($employee, $startDate, $endDate);

        $totalMinutesWorked = 0;
        foreach ($workTimes as $workTime) {
            $interval = $workTime->getStartTime()->diff($workTime->getEndTime());
            $totalMinutesWorked += ($interval->days * 24 * 60) + ($interval->h * 60);
        }

        $roundedMinutes = $this->roundWorkTimeMinutes($totalMinutesWorked);

        $standardMinutes = min($roundedMinutes, $this->monthlyNormMinutes);
        $overtimeMinutes = max(0, $roundedMinutes - $this->monthlyNormMinutes);

        $standardHours = $standardMinutes / 60;
        $overtimeHours = $overtimeMinutes / 60;

        $standardRate = $this->standardRatePln;
        $overtimeRate = $standardRate * $this->overtimeRateMultiplier;

        $totalAmount = ($standardHours * $standardRate) + ($overtimeHours * $overtimeRate);

        return new WorkTimeSummaryOutputDto(
            standardHours: round($standardHours, 2),
            standardRate: number_format($standardRate, 2, ',', ' ') . ' PLN',
            overtimeHours: round($overtimeHours, 2),
            overtimeRate: number_format($overtimeRate, 2, ',', ' ') . ' PLN',
            totalAmount: number_format($totalAmount, 2, ',', ' ') . ' PLN'
        );
    }

    private function roundWorkTimeMinutes(int $totalMinutes): int
    {
        if ($totalMinutes <= 0) {
            return 0;
        }

        $fullHoursMinutes = floor($totalMinutes / 60) * 60;
        $remainingMinutes = $totalMinutes % 60;

        if ($remainingMinutes === 0) {
            return $fullHoursMinutes;
        } elseif ($remainingMinutes <= 15) {
            return $fullHoursMinutes;
        } elseif ($remainingMinutes <= 45) {
            return $fullHoursMinutes + 30;
        } else {
            return $fullHoursMinutes + 60;
        }
    }
}