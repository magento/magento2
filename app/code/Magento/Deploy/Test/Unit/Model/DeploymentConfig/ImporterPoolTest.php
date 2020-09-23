<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\ImporterPool;
use Magento\Deploy\Model\DeploymentConfig\ValidatorFactory;
use Magento\Framework\App\DeploymentConfig\ValidatorInterface;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class ImporterPoolTest extends TestCase
{
    /**
     * @var ImporterPool
     */
    private $configImporterPool;

    /**
     * @var ObjectManagerInterface|Mock
     */
    private $objectManagerMock;

    /**
     * @var ValidatorFactory|Mock
     */
    private $validatorFactoryMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->validatorFactoryMock = $this->getMockBuilder(ValidatorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configImporterPool = new ImporterPool(
            $this->objectManagerMock,
            $this->validatorFactoryMock,
            [
                'firstSection' => ['importer_class' => 'Magento\Importer\SomeImporter', 'sort_order' => 20],
                'secondSection' => [
                    'importer_class' => 'Magento\Importer\SomeImporter',
                    'validator_class' => 'Validator\SomeValidator\Class'
                ],
                'thirdSection' => ['importer_class' => 'Magento\Importer\SomeImporter', 'sort_order' => 10]
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetImporters()
    {
        $expectedResult = [
            'secondSection' => 'Magento\Importer\SomeImporter',
            'thirdSection' => 'Magento\Importer\SomeImporter',
            'firstSection' => 'Magento\Importer\SomeImporter',
        ];
        $this->assertSame($expectedResult, $this->configImporterPool->getImporters());
    }

    /**
     * @return void
     */
    public function testGetImportersEmptyParameterClass()
    {
        $this->expectException('Magento\Framework\Exception\ConfigurationMismatchException');
        $this->expectExceptionMessage(
            'The parameter "importer_class" is missing. Set the "importer_class" and try again.'
        );
        $this->configImporterPool = new ImporterPool(
            $this->objectManagerMock,
            $this->validatorFactoryMock,
            ['wrongSection' => ['class' => '']]
        );

        $this->configImporterPool->getImporters();
    }

    /**
     * @return void
     */
    public function testGetSections()
    {
        $this->assertSame(
            ['firstSection', 'secondSection', 'thirdSection'],
            $this->configImporterPool->getSections()
        );
    }

    public function testGetValidator()
    {
        $validatorMock = $this->getMockBuilder(ValidatorInterface::class)
            ->getMockForAbstractClass();
        $this->validatorFactoryMock->expects($this->once())
            ->method('create')
            ->with('Validator\SomeValidator\Class')
            ->willReturn($validatorMock);

        $this->assertNull($this->configImporterPool->getValidator('firstSection'));
        $this->assertNull($this->configImporterPool->getValidator('thirdSection'));
        $this->assertInstanceOf(
            ValidatorInterface::class,
            $this->configImporterPool->getValidator('secondSection')
        );
    }
}
