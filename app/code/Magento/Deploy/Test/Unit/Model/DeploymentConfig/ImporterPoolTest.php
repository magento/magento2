<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model\DeploymentConfig;

use Magento\Deploy\Model\DeploymentConfig\ImporterPool;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
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
     * @var ImporterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $importerMock;

    /**
     * @var \StdClass
     */
    private $wrongImporter;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->importerMock = $this->getMockBuilder(ImporterInterface::class)
            ->getMockForAbstractClass();
        $this->wrongImporter = new \StdClass();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                ['Magento\Importer\SomeSection', $this->importerMock],
                ['Magento\Importer\WrongSection', $this->wrongImporter],
            ]);
        $this->configImporterPool = new ImporterPool(
            $this->objectManagerMock,
            ['someSection' => 'Magento\Importer\SomeSection']
        );
    }

    /**
     * @return void
     */
    public function testGetImporters()
    {
        $expectedResult = ['someSection' => $this->importerMock];
        $this->assertSame($expectedResult, $this->configImporterPool->getImporters());
    }

    /**
     * @return void
     * @expectedException \Magento\Framework\Exception\ConfigurationMismatchException
     * @codingStandardsIgnoreStart
     * @expectedExceptionMessage wrongSection: Instance of Magento\Framework\App\DeploymentConfig\ImporterInterface is expected, got stdClass instead
     * @codingStandardsIgnoreEnd
     */
    public function testGetImportersWithException()
    {
        $this->configImporterPool = new ImporterPool(
            $this->objectManagerMock,
            ['wrongSection' => 'Magento\Importer\WrongSection']
        );

        $this->configImporterPool->getImporters();
    }

    /**
     * @return void
     */
    public function testGetSections()
    {
        $this->assertSame(['someSection'], $this->configImporterPool->getSections());
    }
}
