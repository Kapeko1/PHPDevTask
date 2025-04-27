<?php

namespace App\Controller\Api;


namespace App\Controller\Api;

use App\Dto\WorkTimeInputDto;
use App\Dto\WorkTimeSummaryInputDto;

use App\Service\WorkTimeService;
use App\Service\WorkTimeSummaryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\ErrorReportingService;

#[Route('/api/v1/work-times')]
class WorkTimeController extends AbstractController
{
    protected ErrorReportingService $errorReportingService;
    public function __construct(ErrorReportingService $errorReportingService)   {
        $this->errorReportingService = $errorReportingService;
    }

    #[Route('', name: 'api_work_time_create', methods: ['POST'])]
    public function create(
        Request             $request,
        SerializerInterface $serializer,
        ValidatorInterface  $validator,
        WorkTimeService     $workTimeService
    ): JsonResponse
    {
        try {
            $workTimeDto = $serializer->deserialize($request->getContent(), WorkTimeInputDto::class, 'json');


        } catch (NotEncodableValueException $e) {
            $errorContext = ['raw_request_content' => $request->getContent()];
            $errorId = $this->errorReportingService->reportError($e, $errorContext);
            return new JsonResponse(['error' => 'Nieprawidłowy format JSON. Kod błędu: '.$errorId], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Throwable $e) {
            $errorContext = ['raw_request_content' => $request->getContent()];
            $errorId = $this->errorReportingService->reportError($e, $errorContext);
            return new JsonResponse(['error' => 'Wystąpił nieoczekiwany błąd'. $errorId], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($workTimeDto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        try {
            $workTime = $workTimeService->create($workTimeDto);
            return new JsonResponse(['response' => "Czas pracy został dodany!"], Response::HTTP_CREATED);
        } catch (\Throwable $e) {

            //FIXME: Find a way to not make such long, awful messages (with 2uuids) when catching throws from WorkTimeService

            $errorContext = ['workTimeDto' => (array) $workTimeDto];
            $errorId = $this->errorReportingService->reportError($e, $errorContext);
            return new JsonResponse(['error' => $e->getMessage()." Kod błędu: ". $errorId], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/summary', name: 'api_work_time_summary', methods: ['GET'])]
    public function getSummary(
        Request $request,
        ValidatorInterface $validator,
        WorkTimeSummaryService $summaryService,
        SerializerInterface $serializer
    ): JsonResponse {
        $inputDto = new WorkTimeSummaryInputDto();
        $inputDto->employeeId = $request->query->get('employeeId');
        $inputDto->dateString = $request->query->get('date');

        $errors = $validator->validate($inputDto);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()][] = $error->getMessage();
            }
            return new JsonResponse(
                ['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        try {
            $summaryDto = $summaryService->calculateSummary($inputDto);
            return new JsonResponse(['response' => $serializer->normalize($summaryDto)], Response::HTTP_OK);
        } catch (\Throwable $e) {
            $errorContext = ['summaryInputDto' => (array) $inputDto];
            $errorId = $this->errorReportingService->reportError($e, $errorContext);
            return new JsonResponse(['error' => 'Wystąpił nieoczekiwany błąd. Kod błędu: '.$errorId], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}