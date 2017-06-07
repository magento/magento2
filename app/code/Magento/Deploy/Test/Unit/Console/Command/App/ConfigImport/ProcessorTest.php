<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Console\Command\App\ConfigImport;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\App\DeploymentConfig;
use Psr\Log\LoggerInterface as Logger;
use Magento\Deploy\Console\Command\App\ConfigImport\Processor;
use Magento\Deploy\Model\DeploymentConfig\ChangeDetector;
use Magento\Deploy\Model\DeploymentConfig\Hash;
use Magento\Deploy\Model\DeploymentConfig\ImporterPool;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Magento\Deploy\Model\DeploymentConfig\ImporterFactory;
use Magento\Framework\Console\QuestionPerformer\YesNo;
use Magento\Framework\App\DeploymentConfig\ValidatorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChangeDetector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $changeDetectorMock;

    /**
     * @var ImporterPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configImporterPoolMock;

    /**
     * @var ImporterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $importerFactoryMock;

    /**
     * @var DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $deploymentConfigMock;

    /**
     * @var Hash|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configHashMock;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $outputMock;

    /**
     * @var InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputMock;

    /**
     * @var YesNo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $questionPerformerMock;

    /**
     * @var Processor
     */
    private $processor;

    protected function setUp()
    {
        $this->importerFactoryMock = $this->getMockBuilder(ImporterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->changeDetectorMock = $this->getMockBuilder(ChangeDetector::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configImporterPoolMock = $this->getMockBuilder(ImporterPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deploymentConfigMock = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configHashMock = $this->getMockBuilder(Hash::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->outputMock = $this->getMockBuilder(OutputInterface::class)
            ->getMockForAbstractClass();
        $this->inputMock = $this->getMockBuilder(InputInterface::class)
            ->getMockForAbstractClass();
        $this->questionPerformerMock = $this->getMockBuilder(YesNo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new Processor(
            $this->changeDetectorMock,
            $this->configImporterPoolMock,
            $this->importerFactoryMock,
            $this->deploymentConfigMock,
            $this->configHashMock,
            $this->loggerMock,
            $this->questionPerformerMock
        );
    }

    /**
     * @param bool $doImport
     * @param bool $skipImport
     * @param array $warningMessages
     * @dataProvider importDataProvider
     */
    public function testImport($doImport, $skipImport, array $warningMessages)
    {
        $configData = ['some data'];
        $messages = ['The import is complete'];
        $expectsMessages = ['The import is complete'];
        $importerClassName = 'someImporterClassName';
        $question = ['Do you want to continue [yes/no]?'];
        $importers = ['someSection' => $importerClassName];
        $importerMock = $this->getMockBuilder(ImporterInterface::class)
            ->getMockForAbstractClass();

        $this->configImporterPoolMock->expects($this->once())
            ->method('getImporters')
            ->willReturn($importers);
        $this->importerFactoryMock->expects($this->once())
            ->method('create')
            ->with($importerClassName)
            ->willReturn($importerMock);
        $this->changeDetectorMock->expects($this->any())
            ->method('hasChanges')
            ->willReturn(true);
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with('someSection')
            ->willReturn($configData);

        $importerMock->expects($this->any())
            ->method('getWarningMessages')
            ->willReturn($warningMessages);
        $this->questionPerformerMock->expects($this->any())
            ->method('execute')
            ->with(array_merge($warningMessages, $question), $this->inputMock, $this->outputMock)
            ->willReturn($doImport);

        $this->loggerMock->expects($this->never())
            ->method('error');

        if ($skipImport) {
            $this->outputMock->expects($this->once())
                ->method('writeln')
                ->with('<info>Processing configurations data from configuration file...</info>');
            $importerMock->expects($this->never())
                ->method('import');
            $this->configHashMock->expects($this->never())
                ->method('regenerate');
        } else {
            $this->outputMock->expects($this->at(0))
                ->method('writeln')
                ->with('<info>Processing configurations data from configuration file...</info>');
            $this->outputMock->expects($this->at(1))
                ->method('writeln')
                ->with($expectsMessages);
            $importerMock->expects($this->once())
                ->method('import')
                ->with($configData)
                ->willReturn($messages);
            $this->configHashMock->expects($this->once())
                ->method('regenerate');
        }

        $this->processor->execute($this->inputMock, $this->outputMock);
    }

    /**
     * @return array
     */
    public function importDataProvider()
    {
        return [
            [
                'doImport' => false,
                'skipImport' => false,
                'warningMessages' => [],
            ],
            [
                'doImport' => true,
                'skipImport' => false,
                'warningMessages' => [],
            ],
            [
                'doImport' => true,
                'skipImport' => false,
                'warningMessages' => ['Some message'],
            ],
            [
                'doImport' => false,
                'skipImport' => true,
                'warningMessages' => ['Some message'],
            ],
        ];
    }

    /**
     * @expectedException \Magento\Framework\Exception\RuntimeException
     * @expectedExceptionMessage Import failed: Some error
     */
    public function testImportWithException()
    {
        $exception = new \Exception('Some error');
        $this->outputMock->expects($this->never())
            ->method('writeln');
        $this->configHashMock->expects($this->never())
            ->method('regenerate');
        $this->changeDetectorMock->expects($this->never())
            ->method('hasChanges');
        $this->deploymentConfigMock->expects($this->never())
            ->method('getConfigData');
        $this->configImporterPoolMock->expects($this->once())
            ->method('getImporters')
            ->willThrowException($exception);
        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with($exception);

        $this->processor->execute($this->inputMock, $this->outputMock);
    }

    public function testImportWithValidation()
    {
        $configData = ['config data'];
        $importerClassName = 'someImporterClassName';
        $importers = ['someSection' => $importerClassName];
        $errorMessages = ['error message'];

        $validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $validatorMock->expects($this->once())
            ->method('validate')
            ->with($configData)
            ->willReturn($errorMessages);
        $this->configImporterPoolMock->expects($this->once())
            ->method('getImporters')
            ->willReturn($importers);
        $this->changeDetectorMock->expects($this->exactly(2))
            ->method('hasChanges')
            ->withConsecutive(
                [],
                ['someSection']
            )
            ->willReturnOnConsecutiveCalls(true, true);
        $this->deploymentConfigMock->expects($this->once())
            ->method('getConfigData')
            ->with('someSection')
            ->willReturn($configData);
        $this->configImporterPoolMock->expects($this->once())
            ->method('getValidator')
            ->willReturn($validatorMock);
        $this->outputMock->expects($this->at(1))
            ->method('writeln')
            ->with($errorMessages);
        $this->importerFactoryMock->expects($this->never())
            ->method('create');

        $this->processor->execute($this->inputMock, $this->outputMock);
    }

    /**
     * @param array $importers
     * @param bool $isValid
     * @dataProvider importNothingToImportDataProvider
     */
    public function testImportNothingToImport(array $importers, $isValid)
    {
        $this->configImporterPoolMock->expects($this->once())
            ->method('getImporters')
            ->willReturn($importers);
        $this->changeDetectorMock->expects($this->any())
            ->method('hasChanges')
            ->willReturn($isValid);
        $this->deploymentConfigMock->expects($this->never())
            ->method('getConfigData');
        $this->configHashMock->expects($this->never())
            ->method('regenerate');
        $this->loggerMock->expects($this->never())
            ->method('error');

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('<info>Nothing to import.</info>');

        $this->processor->execute($this->inputMock, $this->outputMock);
    }

    /**
     * @return array
     */
    public function importNothingToImportDataProvider()
    {
        return [
            ['importers' => [], 'isValid' => false],
            ['importers' => [], 'isValid' => true],
            ['importers' => ['someImporter'], 'isValid' => false],
        ];
    }
}
