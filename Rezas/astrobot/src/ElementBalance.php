<?php

declare(strict_types=1);

namespace Astroinfo\App;

final class ElementBalance
{
    public string $Element;     // Fire, Earth, Air, Water
    public int $Power;          // 1, 4, 2, 7
    public string $PlanetsText; // "Moon (2x), Jupiter, Saturn, Node, MC (2x)"

    public function __construct(string $element, int $power, string $planetsText)
    {
        $this->Element = $element;
        $this->Power = $power;
        $this->PlanetsText = $planetsText;
    }
}
