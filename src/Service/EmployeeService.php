<?php

namespace App\Service;

use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use App\Dto\EmployeeInputDto;

class EmployeeService   {
    protected EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    public function create(EmployeeInputDto $inputDto)  {
        $employee = new Employee();

        if ($inputDto->firstName!=null && $inputDto->lastName!=null)  {
            $employee->setFirstName($inputDto->firstName);
            $employee->setLastName($inputDto->lastName);
        }

        $this->entityManager->persist($employee);
        $this->entityManager->flush();

        return $employee;
    }

}
