<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\SearchEngine;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDeclaredFeatures()
    {
        $xmlPath = __DIR__ . '/../../_files/search_engine.xml';
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $fileResolver = $this->getMockForAbstractClass('Magento\Framework\Config\FileResolverInterface', [], '', false);
        $fileResolver->expects($this->once())->method('get')->willReturn([file_get_contents($xmlPath)]);
        $configReader = $objectManager->create(
            'Magento\Framework\Search\SearchEngine\Config\Reader',
            ['fileResolver' => $fileResolver]
        );
        $dataStorage = $objectManager->create(
            'Magento\Search\Model\SearchEngine\Config\Data',
            ['reader' => $configReader]
        );
        $config = $objectManager->create('Magento\Search\Model\SearchEngine\Config', ['dataStorage' => $dataStorage]);
        $this->assertEquals(['synonyms'], $config->getDeclaredFeatures('mysql'));
        $this->assertEquals(['synonyms', 'stopword'], $config->getDeclaredFeatures('other'));
        $this->assertEquals([], $config->getDeclaredFeatures('none'));
        $this->assertEquals([], $config->getDeclaredFeatures('non_exist'));
    }
}
