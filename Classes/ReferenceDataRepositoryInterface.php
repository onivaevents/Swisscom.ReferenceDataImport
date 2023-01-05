<?php

declare(strict_types=1);

namespace Swisscom\ReferenceDataImport;

use Neos\Flow\Persistence\RepositoryInterface;

interface ReferenceDataRepositoryInterface extends RepositoryInterface
{
    public function findByReferenceDataEntity(object $object): ?object;
}
