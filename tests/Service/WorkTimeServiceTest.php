<?php

namespace App\Tests\Service;

use App\Dto\WorkTimeInputDto;
use App\Entity\Employee;
use App\Entity\WorkTime;
use App\Repository\EmployeeRepository;
use App\Repository\WorkTimeRepository;
use App\Service\ErrorReportingService;
use App\Service\WorkTimeService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class WorkTimeServiceTest extends TestCase
{
    protected $entityManagerMock;
    protected $employeeRepositoryMock;
    protected $workTimeRepositoryMock;
    protected $errorReportingServiceMock;
    protected WorkTimeService $workTimeService;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->employeeRepositoryMock = $this->createMock(EmployeeRepository::class);
        $this->workTimeRepositoryMock = $this->createMock(WorkTimeRepository::class);
        $this->errorReportingServiceMock = $this->createMock(ErrorReportingService::class);

        $this->workTimeService = new WorkTimeService(
            $this->entityManagerMock,
            $this->employeeRepositoryMock,
            $this->workTimeRepositoryMock,
            $this->errorReportingServiceMock
        );
    }

    public function testCreateWorkTimeSuccessfully(): void  {
        $employeeId = Uuid::v4();
        $employee = new Employee();
        $employee->setId($employeeId);

        $inputDto = new WorkTimeInputDto();
        $inputDto->employeeId = $employeeId->toString();
        $inputDto->startTimeString = '27.04.2025 08:00';
        $inputDto->endTimeString = '27.04.2025 16:00';

        $this->employeeRepositoryMock->method('find')
            ->with($employeeId)
            ->willReturn($employee);

        $expectedStartDay = (new DateTimeImmutable('2025-04-27'));
        $this->workTimeRepositoryMock->method('hasMultipleEntriesPerEmployeePerDay')
            ->with($employee, $expectedStartDay)
            ->willReturn(true);

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(WorkTime::class));

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $result = $this->workTimeService->create($inputDto);

        $this->assertInstanceOf(WorkTime::class, $result);
        $this->assertSame($employee, $result->getEmployee());
        $this->assertEquals(new DateTimeImmutable('2025-04-27 08:00:00'), $result->getStartTime());
        $this->assertEquals(new DateTimeImmutable('2025-04-27 16:00:00'), $result->getEndTime());
        $this->assertEquals($expectedStartDay, $result->getStartDay());
    }

}
