<?php

declare(strict_types=1);

namespace Swisscom\ReferenceDataImport\Tests\Functional\Fixture;

use Neos\Flow\Annotations as Flow;
use Swisscom\ReferenceDataImport\Annotation as ReferenceData;

/**
 * @Flow\Entity
 * @ReferenceData\Entity
 */
class Dummy
{
    /**
     * @ReferenceData\Updatable
     */
    public $property;

    public function __construct($property = null)
    {
        $this->property = $property;
    }
}
