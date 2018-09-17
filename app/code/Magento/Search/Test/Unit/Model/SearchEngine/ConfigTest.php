<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model\SearchEngine;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Search\Model\SearchEngine\Config\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataStorageMock;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->dataStorage = $this->getMock('Magento\Search\Model\SearchEngine\Config\Data', [], [], '', false);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGetDeclaredFeatures()
    {
        $config = $this->objectManager->getObject(
            '\Magento\Search\Model\SearchEngine\Config',
            ['dataStorage' => $this->dataStorage]
        );
        $this->dataStorage->expects($this->once())->method('get')->with('mysql')->willReturn(['synonyms']);
        $this->assertEquals(['synonyms'], $config->getDeclaredFeatures('mysql'));
    }

    public function testIsFeatureSupported()
    {
        $config = $this->objectManager->getObject(
            '\Magento\Search\Model\SearchEngine\Config',
            ['dataStorage' => $this->dataStorage]
        );
        $this->dataStorage->expects($this->once())->method('get')->with('mysql')->willReturn(['synonyms']);
        $this->assertEquals(true, $config->isFeatureSupported('synonyms', 'mysql'));
    }
}
