<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;

class WorkTimeInputDto  {
    #[Assert\NotBlank(message: "Identyfikator nie może być pusty.")]
    #[Assert\Uuid(message: "Identyfikator musi być poprawnym UUID.")]
    #[SerializedName('unikalny_identyfikator_pracownika')]
    public ?string $employeeId = null;

    #[Assert\NotBlank(message: "Początek czasu pracy nie może być pusty.")]
    #[SerializedName('data_i_godzina_rozpoczęcia')]
    public ?string $startTimeString = null;

    #[Assert\NotBlank(message: "Koniec czasu pracy nie może być pusty.")]
    #[SerializedName('data_i_godzina_zakonczenia')]
    public ?string $endTimeString = null;
}
