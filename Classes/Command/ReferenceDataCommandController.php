<?php

declare(strict_types=1);

namespace Swisscom\ReferenceDataImport\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Utility\ObjectAccess;
use Swisscom\AliceConnector\Context;
use Swisscom\ReferenceDataImport\Annotation\Entity;
use Swisscom\ReferenceDataImport\Annotation\Updatable;
use Swisscom\ReferenceDataImport\Exception;
use Swisscom\ReferenceDataImport\ReferenceDataRepositoryInterface;
use Swisscom\ReferenceDataImport\SignalEmitter;

/**
 * @Flow\Scope("singleton")
 */
class ReferenceDataCommandController extends CommandController
{
    protected Context $context;

    /**
     * @Flow\Inject
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var ReflectionService
     */
    protected $reflectionService;

    /**
     * @Flow\Inject
     * @var SignalEmitter
     */
    protected $signalEmitter;

    /**
     * @Flow\InjectConfiguration(path="defaultFixtureName")
     * @var string
     */
    protected $defaultFixtureName;

    protected function initializeObject(): void
    {
        $this->context = new Context($this->objectManager, false);
    }

    public function importCommand(?string $fixtureName = null): void
    {
        if ($fixtureName === null) {
            $fixtureName = $this->defaultFixtureName;
        }

        $this->signalEmitter->emitBeforeLoadFixture();

        $objects = $this->context->loadFixture($fixtureName, 'referenceData');

        foreach ($objects as $object) {
            if (!$this->reflectionService->isClassAnnotatedWith($object::class, Entity::class)) {
                /* Not annotated objects are simply skipped. Those may be referenced by other objects and persisted
                through cascading. */
                continue;
            }

            $repository = $this->getRepository($object);
            if ($existingObject = $repository->findByReferenceDataEntity($object)) {
                $properties = $this->reflectionService->getPropertyNamesByAnnotation(
                    $existingObject::class,
                    Updatable::class
                );
                if ($properties) {
                    $this->signalEmitter->emitBeforeUpdate($existingObject, $object);
                    foreach ($properties as $property) {
                        $value = ObjectAccess::getProperty($object, $property, true);
                        ObjectAccess::setProperty($existingObject, $property, $value);
                    }
                    $repository->update($existingObject);
                    $message = 'properties updated';
                } else {
                    $this->signalEmitter->emitBeforeSkip($existingObject, $object);
                    $message = 'no updatable properties';
                }
                $id = $this->persistenceManager->getIdentifierByObject($existingObject);
                $this->outputLine('ReferenceData entity "%s" id "%s": %s', [$existingObject::class, $id, $message]);
            } else {
                $this->signalEmitter->emitBeforeAdd($object);
                $repository->add($object);
                $this->outputLine('ReferenceData entity "%s": object added', [$object::class]);
            }
        }

        $this->signalEmitter->emitBeforePersist();
    }

    private function getRepository(object $object): ReferenceDataRepositoryInterface
    {
        $repositoryClassName = (string)preg_replace(
            ['/\\\Model\\\/', '/$/'],
            ['\\Repository\\', 'Repository'],
            get_class($object)
        );
        $repository = $this->objectManager->get($repositoryClassName);

        if (!($repository instanceof ReferenceDataRepositoryInterface)) {
            throw new Exception(sprintf(
                'The repository "%s" should implement "%s"',
                $repository::class,
                ReferenceDataRepositoryInterface::class,
            ));
        }

        return $repository;
    }
}
