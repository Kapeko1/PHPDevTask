<?php

namespace App\Service;

use App\Entity\ErrorReport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Uid\Uuid;
use Throwable;


class ErrorReportingService {
    protected EntityManagerInterface $entityManager;
    protected RequestStack $requestStack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack) {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    public function reportError(Throwable $exception, ?array $context = null): string   {
        $errorReport = new ErrorReport();
        $errorReport->setErrorClass(get_class($exception));
        $errorReport->setErrorMessage($exception->getMessage());
        $errorReport->setErrorCode($exception->getCode());
        $errorReport->setTrace($exception->getTraceAsString());
        $errorReport->setContext($context);

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $errorReport->setRequestUri($request->getRequestUri());
        }
        $this->entityManager->persist($errorReport);
        $this->entityManager->flush();

        $errorId = $errorReport->getId();
        return $errorId->toString();
    }
}