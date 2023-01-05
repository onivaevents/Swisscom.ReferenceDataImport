<?php

declare(strict_types=1);

namespace Swisscom\ReferenceDataImport\Tests\Functional;

use Neos\Flow\Cli\ConsoleOutput;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Tests\FunctionalTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Swisscom\ReferenceDataImport\Command\ReferenceDataCommandController;
use Swisscom\ReferenceDataImport\ReferenceDataRepositoryInterface;
use Swisscom\ReferenceDataImport\Tests\Functional\Fixture\DummyRepository;

class ReferenceDataCommandControllerTest extends FunctionalTestCase
{
    /** @var ReferenceDataCommandController  */
    protected $obj;

    /** @var ReferenceDataRepositoryInterface&MockObject */
    protected ReferenceDataRepositoryInterface $dummyRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->obj = $this->objectManager->get(ReferenceDataCommandController::class);

        $this->inject($this->obj, 'output', $this->createMock(ConsoleOutput::class));

        $this->dummyRepositoryMock = $this->createMock(DummyRepository::class);
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->expects(self::exactly(2))->method('get')->with(DummyRepository::class)
            ->willReturn($this->dummyRepositoryMock);
        $this->inject($this->obj, 'objectManager', $objectManagerMock);
    }

    /**
     * @test
     */
    public function importNewObjectsTest(): void
    {
        $this->dummyRepositoryMock->expects(self::never())->method('update');
        $this->dummyRepositoryMock->expects(self::exactly(2))->method('add');

        $this->obj->importCommand('Dummy');
    }
}
