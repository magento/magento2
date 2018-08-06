<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model\SearchEngine;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Search\Model\SearchEngine\Config\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataStorageMock;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->dataStorage = $this->createMock(\Magento\Search\Model\SearchEngine\Config\Data::class);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGetDeclaredFeatures()
    {
        $config = $this->objectManager->getObject(
            \Magento\Search\Model\SearchEngine\Config::class,
            ['dataStorage' => $this->dataStorage]
        );
        $this->dataStorage->expects($this->once())->method('get')->with('mysql')->willReturn(['synonyms']);
        $this->assertEquals(['synonyms'], $config->getDeclaredFeatures('mysql'));
    }

    public function testIsFeatureSupported()
    {
        $config = $this->objectManager->getObject(
            \Magento\Search\Model\SearchEngine\Config::class,
            ['dataStorage' => $this->dataStorage]
        );
        $this->dataStorage->expects($this->once())->method('get')->with('mysql')->willReturn(['synonyms']);
        $this->assertEquals(true, $config->isFeatureSupported('synonyms', 'mysql'));
    }
}
