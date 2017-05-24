<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\ImporterPool;
use Magento\Framework\ObjectManagerInterface;

class ImporterPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImporterPool
     */
    private $configImporterPool;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->configImporterPool = new ImporterPool(
            $this->objectManagerMock,
            [
                'firstSection' => ['class' => 'Magento\Importer\SomeImporter', 'sortOrder' => 20],
                'secondSection' => ['class' => 'Magento\Importer\SomeImporter'],
                'thirdSection' => ['class' => 'Magento\Importer\SomeImporter', 'sortOrder' => 10]
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
     * @expectedException \Magento\Framework\Exception\ConfigurationMismatchException
     * @expectedExceptionMessage Parameter "class" must be present.
     */
    public function testGetImportersEmptyParameterClass()
    {
        $this->configImporterPool = new ImporterPool(
            $this->objectManagerMock,
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
}
