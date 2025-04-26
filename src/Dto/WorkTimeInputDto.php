<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;

class WorkTimeInputDto  {
    public const DATE_FORMAT = 'd.m.Y H:i';
        private const DATE_REGEX = '/^\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}$/';


    #[Assert\NotBlank(message: "Identyfikator nie może być pusty.")]
    #[Assert\Uuid(message: "Identyfikator musi być poprawnym UUID.")]
    #[SerializedName('unikalny_identyfikator_pracownika')]
    public ?string $employeeId = null;

    #[Assert\NotBlank(message: "Początek czasu pracy nie może być pusty.")]
    #[Assert\Regex(
        pattern: self::DATE_REGEX,
        message: "Początek czasu pracy musi być w formacie 'dd.mm.yyyy hh:mm'.")]
    #[SerializedName('data_i_godzina_rozpoczecia')]
    public ?string $startTimeString = null;

    #[Assert\NotBlank(message: "Koniec czasu pracy nie może być pusty.")]
    #[Assert\Regex(
        pattern: self::DATE_REGEX,
        message: "Koniec czasu pracy musi być w formacie 'dd.mm.yyyy hh:mm'."
    )]
    #[SerializedName('data_i_godzina_zakonczenia')]
    public ?string $endTimeString = null;
}
