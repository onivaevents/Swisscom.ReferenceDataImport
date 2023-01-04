<?php

declare(strict_types=1);

namespace Swisscom\ReferenceDataImport\Tests\Functional;

use Neos\Flow\Cli\ConsoleOutput;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Flow\Tests\FunctionalTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Swisscom\ReferenceDataImport\Command\ReferenceDataCommandController;
use Swisscom\ReferenceDataImport\ReferenceDataRepositoryInterface;

class ReferenceDataCommandControllerTest extends FunctionalTestCase
{
    /** @var ReferenceDataCommandController  */
    protected $obj;

    /** @var PersistenceManagerInterface&MockObject */
    protected PersistenceManagerInterface $persistenceManagerMock;

    /** @var ReferenceDataRepositoryInterface&MockObject */
    protected ReferenceDataRepositoryInterface $referenceDataRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->obj = $this->objectManager->get(ReferenceDataCommandController::class);

        $this->inject($this->obj, 'output', $this->createMock(ConsoleOutput::class));
        $this->persistenceManagerMock = $this->createMock(PersistenceManagerInterface::class);
        $this->inject($this->obj, 'persistenceManager', $this->persistenceManagerMock);

        $this->referenceDataRepository = $this->createMock(ReferenceDataRepositoryInterface::class);
    }

    /**
     * @test
     */
    public function importNewObjectsTest(): void
    {
        $this->persistenceManagerMock->expects(self::never())->method('update');
        $this->persistenceManagerMock->expects(self::exactly(2))->method('add');

        $this->obj->importCommand('Dummy');
    }
}
