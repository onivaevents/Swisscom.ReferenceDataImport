<?php

declare(strict_types=1);

namespace Swisscom\ReferenceDataImport\Tests\Unit\Fixture;

class Dummy
{
    public $property;

    public function __construct($property = null)
    {
        $this->property = $property;
    }
}
