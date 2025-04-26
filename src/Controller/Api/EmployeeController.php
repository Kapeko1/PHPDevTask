<?php

namespace App\Controller\Api;

use App\Service\EmployeeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Dto\EmployeeInputDto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Uid\Uuid;


#[Route('/api/v1/employees', name: 'employees')]
class EmployeeController extends AbstractController {
    #[Route('', name: 'api_employee_create', methods: ['POST'])]
    public function createEmployee(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        EmployeeService $employeeService,
    ): JsonResponse    {
        try {
            $employeeDto = $serializer->deserialize($request->getContent(), EmployeeInputDto::class, 'json');

        } catch (NotEncodableValueException $e) {
            return new JsonResponse(['error' => 'Nieprawidłowy format JSON'], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Wystąpił nieoczekiwany błąd'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($employeeDto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        try{
            $employee = $employeeService->create($employeeDto);
        } catch (\Throwable $e) {
            return new JsonResponse(['error'=>'Wystąpił błąd podczas tworzenia użytkownika'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $employeeId=$employee->getId();
        if (!$employeeId instanceof Uuid) {
            return new JsonResponse(['error'=> 'Wystąpił błąd podczas pobierania identyfikatora pracownika po utworzeniu.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['id'=>$employeeId->toString()], Response::HTTP_CREATED);
    }
}