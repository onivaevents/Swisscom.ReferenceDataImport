<?php

declare(strict_types=1);

namespace Swisscom\ReferenceDataImport;

interface ReferenceDataRepositoryInterface
{
    public function findByReferenceDataEntity(object $object): ?object;
}
