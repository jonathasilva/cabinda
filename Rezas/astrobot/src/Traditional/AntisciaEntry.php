<?php

declare(strict_types=1);

namespace Astroinfo\App\Traditional;

final class AntisciaEntry
{
    public string $Point;

    public AntisciaPart $Antiscia;
    public AntisciaPart $ContraAntiscia;

    public function __construct(string $point, AntisciaPart $antiscia, AntisciaPart $contraAntiscia)
    {
        $this->Point = $point;
        $this->Antiscia = $antiscia;
        $this->ContraAntiscia = $contraAntiscia;
    }
}
