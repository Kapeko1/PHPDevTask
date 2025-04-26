<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;


class WorkTimeSummaryInputDto   {
    #[Assert\NotBlank(message: "Identyfikator pracownika nie może być pusty.")]
    #[Assert\Uuid(message: "Identyfikator pracownika musi być poprawnym UUID.")]
    #[SerializedName('unikalny_identyfikator_pracownika')]
    public ?string $employeeId = null;

    #[Assert\NotBlank(message: "Data nie może być pusta.")]
    #[Assert\Regex(
        pattern: "/^(0[1-9]|1[0-2])-\d{4}$/",
        message: "Data musi być w formacie 'MM-YYYY'."
    )]
    #[SerializedName('data')]
    public ?string $dateString = null;
}