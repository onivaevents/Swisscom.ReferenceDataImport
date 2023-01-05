<?php

declare(strict_types=1);

namespace Swisscom\ReferenceDataImport\Tests\Functional\Fixture;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\Repository;
use Swisscom\ReferenceDataImport\ReferenceDataRepositoryInterface;

/**
 * @Flow\Scope("singleton")
 */
class DummyRepository extends Repository implements ReferenceDataRepositoryInterface
{
    public function findByReferenceDataEntity(object $object): ?object
    {
        return null;
    }
}
