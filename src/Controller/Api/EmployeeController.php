<?php

namespace App\Controller\Api;

use App\Service\EmployeeService;
use Exception;
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
use App\Service\ErrorReportingService;


#[Route('/api/v1/employees', name: 'employees')]
class EmployeeController extends AbstractController {
    protected ErrorReportingService $errorReportingService;

    public function __construct(ErrorReportingService $errorReportingService) {
        $this->errorReportingService = $errorReportingService;
    }

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
            $errorContext = ['raw_request_content' => $request->getContent()];
            $errorId = $this->errorReportingService->reportError($e, $errorContext);
            return new JsonResponse(['error' => 'Nieprawidłowy format JSON. Kod błędu: '. $errorId], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            $errorContext = ['raw_request_content' => $request->getContent()];
            $errorId = $this->errorReportingService->reportError($e, $errorContext);
            return new JsonResponse(['error' => 'Wystąpił nieoczekiwany błąd. Kod błędu: '. $errorId], Response::HTTP_BAD_REQUEST);
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
            $errorContext = ['employeeDto' => (array) $employeeDto];
            $errorId = $this->errorReportingService->reportError($e, $errorContext);
            return new JsonResponse(['error'=>'Wystąpił błąd podczas tworzenia użytkownika. Kod błędu: ', $errorId], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $employeeId=$employee->getId();
        if (!$employeeId instanceof Uuid) {
            $loggingException = new Exception("Failed to retrieve Uuid after creating Employee");
            $errorContext = [
                'employeeDto' => (array) $employeeDto,
                'createdEmployeeObjectState' => $employee ? json_encode($employee) : null
            ];
            $errorId = $this->errorReportingService->reportError($loggingException, $errorContext);

            return new JsonResponse(['error'=> 'Wystąpił błąd podczas pobierania identyfikatora pracownika po utworzeniu. Kod błędu: ', $errorId], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['id'=>$employeeId->toString()], Response::HTTP_CREATED);
    }
}