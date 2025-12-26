<?php

declare(strict_types=1);

namespace Astroinfo\App\Traditional;

final class DodecatemoriaPart
{
    public string $Sign;
    public int $Degree;
    public int $Minute;

    public function __construct(string $sign, int $degree, int $minute)
    {
        $this->Sign = $sign;
        $this->Degree = $degree;
        $this->Minute = $minute;
    }
}
