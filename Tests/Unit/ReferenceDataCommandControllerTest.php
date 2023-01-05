<?php

declare(strict_types=1);

namespace Swisscom\ReferenceDataImport\Tests\Unit;

use Neos\Flow\Cli\ConsoleOutput;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Flow\Tests\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Swisscom\AliceConnector\Context;
use Swisscom\ReferenceDataImport\Annotation\Entity;
use Swisscom\ReferenceDataImport\Command\ReferenceDataCommandController;
use Swisscom\ReferenceDataImport\ReferenceDataRepositoryInterface;
use Swisscom\ReferenceDataImport\SignalEmitter;
use Swisscom\ReferenceDataImport\Tests\Unit\Fixture\Dummy;

class ReferenceDataCommandControllerTest extends UnitTestCase
{
    /** @var ReferenceDataCommandController  */
    protected $obj;

    /** @var Context&MockObject */
    protected Context $context;

    /** @var ObjectManagerInterface&MockObject */
    protected ObjectManagerInterface $objectManager;

    /** @var PersistenceManagerInterface&MockObject */
    protected PersistenceManagerInterface $persistenceManager;

    /** @var ReflectionService&MockObject */
    protected ReflectionService $reflectionService;

    /** @var SignalEmitter&MockObject */
    protected SignalEmitter $signalEmitter;

    /** @var ReferenceDataRepositoryInterface&MockObject */
    protected ReferenceDataRepositoryInterface $referenceDataRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->obj = new ReferenceDataCommandController();

        $this->inject($this->obj, 'output', $this->createMock(ConsoleOutput::class));
        $this->inject($this->obj, 'persistenceManager', $this->createMock(PersistenceManagerInterface::class));
        $this->context = $this->createMock(Context::class);
        $this->inject($this->obj, 'context', $this->context);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->inject($this->obj, 'objectManager', $this->objectManager);
        $this->reflectionService = $this->createMock(ReflectionService::class);
        $this->inject($this->obj, 'reflectionService', $this->reflectionService);
        $this->signalEmitter = $this->createMock(SignalEmitter::class);
        $this->inject($this->obj, 'signalEmitter', $this->signalEmitter);

        $this->referenceDataRepository = $this->createMock(ReferenceDataRepositoryInterface::class);
    }

    /**
     * @test
     */
    public function importNewObjectTest(): void
    {
        $fixture = new Dummy('Foo');

        $this->context->expects(self::once())->method('loadFixture')
            ->with('Dummy', 'referenceData')
            ->willReturn([$fixture]);

        $this->reflectionService->expects(self::once())->method('isClassAnnotatedWith')
            ->with(Dummy::class, Entity::class)
            ->willReturn(true);

        $this->objectManager->expects(self::once())->method('get')->willReturn($this->referenceDataRepository);
        $this->referenceDataRepository->expects(self::once())->method('findByReferenceDataEntity')
            ->with($fixture)
            ->willReturn(null);

        $this->referenceDataRepository->expects(self::never())->method('update')->with($fixture);
        $this->referenceDataRepository->expects(self::once())->method('add')->with($fixture);
        $this->signalEmitter->expects(self::never())->method('emitBeforeUpdate');
        $this->signalEmitter->expects(self::once())->method('emitBeforeAdd')->with($fixture);
        $this->signalEmitter->expects(self::once())->method('emitBeforePersist');

        $this->obj->importCommand('Dummy');

        self::assertSame($fixture->property, 'Foo');
    }

    /**
     * @test
     */
    public function importExistingObjectTest(): void
    {
        $fixture = new Dummy('Foo');
        $fixtureExisting = new Dummy('Bar');

        $this->context->expects(self::once())->method('loadFixture')
            ->with('Dummy', 'referenceData')
            ->willReturn([$fixture]);

        $this->reflectionService->expects(self::once())->method('isClassAnnotatedWith')
            ->with(Dummy::class, Entity::class)
            ->willReturn(true);

        $this->objectManager->expects(self::once())->method('get')->willReturn($this->referenceDataRepository);
        $this->referenceDataRepository->expects(self::once())->method('findByReferenceDataEntity')
            ->with($fixture)
            ->willReturn($fixtureExisting);
        $this->reflectionService->expects(self::once())->method('getPropertyNamesByAnnotation')->willReturn(['property']);

        $this->referenceDataRepository->expects(self::once())->method('update')->with($fixture);
        $this->referenceDataRepository->expects(self::never())->method('add')->with($fixture);
        $this->signalEmitter->expects(self::once())->method('emitBeforeUpdate')->with($fixtureExisting, $fixture);
        $this->signalEmitter->expects(self::never())->method('emitBeforeAdd');
        $this->signalEmitter->expects(self::once())->method('emitBeforePersist');

        $this->obj->importCommand('Dummy');

        self::assertSame($fixtureExisting->property, 'Foo');
    }
}
