<?php

namespace App\Tests\Service;

use App\Dto\EmployeeInputDto;
use App\Entity\Employee;
use App\Service\EmployeeService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class EmployeeServiceTest extends TestCase
{
    private $entityManagerMock;
    private EmployeeService $employeeService;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->employeeService = new EmployeeService($this->entityManagerMock);
    }

    public function testCreateEmployeeSuccessfully(): void  {
        $inputDto = new EmployeeInputDto();
        $inputDto->firstName = "Karol";
        $inputDto->lastName = "Szabat";

        $this->entityManagerMock->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($employee) use ($inputDto) {
                return $employee instanceof Employee &&
                    $employee->getFirstName() === $inputDto->firstName &&
                    $employee->getLastName() === $inputDto->lastName;
            }));
        $this->entityManagerMock->expects($this->once())
            ->method('flush');
        $result = $this->employeeService->create($inputDto);


        $this->assertInstanceOf(Employee::class, $result);
        $this->assertEquals('Karol', $result->getFirstName());
        $this->assertEquals('Szabat', $result->getLastName());
    }
}
