<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;


class EmployeeInputDto  {
    #[Assert\NotBlank(message: "Imię nie może być puste")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Imię nie może być dłuższe niż 255 znaków",)]
    #[SerializedName("imie")]
    public ?string $firstName = null;

    #[Assert\NotBlank(message: "Nazwisko nie może być puste")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Nazwisko nie może być dłuższe niż 255 znaków",)]
    #[SerializedName("nazwisko")]
    public ?string $lastName = null;
}