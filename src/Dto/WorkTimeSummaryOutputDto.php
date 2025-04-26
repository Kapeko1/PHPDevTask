<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\SerializedName;

class WorkTimeSummaryOutputDto
{
    #[SerializedName("ilosc_normalnych_godzin")]
    public float $standardHours;

    #[SerializedName("stawka")]
    public string $standardRate;

    #[SerializedName("ilosc_nadgodzin")]
    public float $overtimeHours;

    #[SerializedName("stawka_nadgodzinowa")]
    public string $overtimeRate;

    #[SerializedName("suma_po_przeliczeniu")]
    public string $totalAmount;

    public function __construct(
        float $standardHours,
        string $standardRate,
        float $overtimeHours,
        string $overtimeRate,
        string $totalAmount
    ) {
        $this->standardHours = $standardHours;
        $this->standardRate = $standardRate;
        $this->overtimeHours = $overtimeHours;
        $this->overtimeRate = $overtimeRate;
        $this->totalAmount = $totalAmount;
    }
}