<?php

namespace App\Service;

use App\DTO\WorkTimeInputDto;
use App\Entity\WorkTime;
use App\Repository\EmployeeRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Uid\Uuid;

class WorkTimeService   {
    protected EntityManagerInterface $entityManager;
    protected EmployeeRepository $employeeRepository;

    private const MAX_WORK_HOURS = 12;
    public function __construct(EntityManagerInterface $entityManager, EmployeeRepository $employeeRepository)  {
        $this->entityManager = $entityManager;
        $this->employeeRepository = $employeeRepository;
    }

    public function create(WorkTimeInputDto $inputDto): WorkTime    {
        $employee = $this->employeeRepository->find(Uuid::fromString($inputDto->employeeId));

        if(!$employee)  {
            throw new NotFoundHttpException("Pracownik z ID: ". $inputDto->employeeId." nie został znaleziony");
        }
        $startTime = DateTimeImmutable::createFromFormat(WorkTimeInputDto::DATE_FORMAT, $inputDto->startTimeString);
        $endTime = DateTimeImmutable::createFromFormat(WorkTimeInputDto::DATE_FORMAT, $inputDto->endTimeString);


        if ($endTime <= $startTime) {
            throw new UnprocessableEntityHttpException("Czas zakończenia nie może być wcześniejszy niż czas rozpoczęcia.");
        }

        $durationInterval = $startTime->diff($endTime);
        if ($durationInterval->h > self::MAX_WORK_HOURS) {
            throw new UnprocessableEntityHttpException("Czas pracy nie może być dłuższy niż " . self::MAX_WORK_HOURS . " godzin.");
        }

        $startDay = $startTime->setTime(0, 0, 0);

        $workTime = new WorkTime();
        $workTime->setEmployee($employee);
        $workTime->setStartTime($startTime);
        $workTime->setEndTime($endTime);
        $workTime->setStartDay($startDay);

        try {
            $this->entityManager->persist($workTime);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new ConflictHttpException("Pracownik {$employee->getId()} ma już wpisany czas pracy dla dnia {$startDay->format('Y-m-d')}.", $e);
        }
        return $workTime;
    }

}