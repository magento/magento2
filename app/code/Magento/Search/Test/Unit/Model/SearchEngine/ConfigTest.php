<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model\SearchEngine;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\SearchEngine\Config;
use Magento\Search\Model\SearchEngine\Config\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /** @var Data|MockObject */
    protected $dataStorageMock;

    /** @var ObjectManager */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->dataStorage = $this->createMock(Data::class);
        $this->objectManager = new ObjectManager($this);
    }

    public function testGetDeclaredFeatures()
    {
        $config = $this->objectManager->getObject(
            Config::class,
            ['dataStorage' => $this->dataStorageMock]
        );
        $this->dataStorageMock->expects($this->once())->method('get')->with('mysql')->willReturn(['synonyms']);
        $this->assertEquals(['synonyms'], $config->getDeclaredFeatures('mysql'));
    }

    public function testIsFeatureSupported()
    {
        $config = $this->objectManager->getObject(
            Config::class,
            ['dataStorage' => $this->dataStorageMock]
        );
        $this->dataStorageMock->expects($this->once())->method('get')->with('mysql')->willReturn(['synonyms']);
        $this->assertEquals(true, $config->isFeatureSupported('synonyms', 'mysql'));
    }
}
