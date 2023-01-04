<?php

declare(strict_types=1);

namespace Swisscom\ReferenceDataImport\Tests\Functional\Fixture;

use Swisscom\ReferenceDataImport\ReferenceDataRepositoryInterface;

class DummyRepository implements ReferenceDataRepositoryInterface
{
    public function findByReferenceDataEntity(object $object): ?object
    {
        return null;
    }
}
