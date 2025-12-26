<?php

declare(strict_types=1);

namespace Astroinfo\App\Traditional;

final class DodecatemoriaEntry
{
    public string $Point;

    public DodecatemoriaPart $TwelfthPart;
    public DodecatemoriaPart $NinthPart;

    public function __construct(string $point, DodecatemoriaPart $twelfthPart, DodecatemoriaPart $ninthPart)
    {
        $this->Point = $point;
        $this->TwelfthPart = $twelfthPart;
        $this->NinthPart = $ninthPart;
    }
}
