<?php

declare(strict_types=1);

namespace Swisscom\ReferenceDataImport;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Scope("singleton")
 */
class SignalEmitter
{
    /**
     * @Flow\Signal
     * @param object $existingObject
     * @param object $newObject
     */
    public function emitBeforeUpdate($existingObject, $newObject): void
    {
    }

    /**
     * @Flow\Signal
     * @param object $object
     */
    public function emitBeforeAdd($object): void
    {
    }

    /**
     * @Flow\Signal
     */
    public function emitBeforePersist(): void
    {
    }
}
