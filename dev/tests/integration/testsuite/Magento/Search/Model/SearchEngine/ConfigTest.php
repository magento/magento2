<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\SearchEngine;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $xmlPath = __DIR__ . '/../../_files/search_engine.xml';
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $fileResolver = $this->getMockForAbstractClass(
            'Magento\Framework\Config\FileResolverInterface',
            [],
            '',
            false
        );
        $fileResolver->expects($this->any())->method('get')->willReturn([file_get_contents($xmlPath)]);

        $configReader = $objectManager->create(
            'Magento\Framework\Search\SearchEngine\Config\Reader',
            ['fileResolver' => $fileResolver]
        );
        $dataStorage = $objectManager->create(
            'Magento\Search\Model\SearchEngine\Config\Data',
            ['reader' => $configReader]
        );
        $this->config = $objectManager->create(
            'Magento\Search\Model\SearchEngine\Config',
            ['dataStorage' => $dataStorage]
        );
    }

    public function testGetDeclaredFeatures()
    {
        $this->assertEquals(['synonyms'], $this->config->getDeclaredFeatures('mysql'));
        $this->assertEquals(['synonyms', 'stopwords'], $this->config->getDeclaredFeatures('other'));
        $this->assertEquals([], $this->config->getDeclaredFeatures('none1'));
        $this->assertEquals([], $this->config->getDeclaredFeatures('none2'));
        $this->assertEquals([], $this->config->getDeclaredFeatures('non_exist'));
    }

    public function testIsFeatureSupported()
    {
        $this->assertEquals(true, $this->config->isFeatureSupported('synonyms', 'mysql'));
        $this->assertEquals(false, $this->config->isFeatureSupported('stopwords', 'mysql'));
        $this->assertEquals(true, $this->config->isFeatureSupported('synonyms', 'other'));
        $this->assertEquals(true, $this->config->isFeatureSupported('stopwords', 'other'));
        $this->assertEquals(false, $this->config->isFeatureSupported('synonyms', 'none1'));
        $this->assertEquals(false, $this->config->isFeatureSupported('stopwords', 'none1'));
        $this->assertEquals(false, $this->config->isFeatureSupported('synonyms', 'none2'));
        $this->assertEquals(false, $this->config->isFeatureSupported('stopwords', 'none2'));
        $this->assertEquals(false, $this->config->isFeatureSupported('synonyms', 'non_exist'));
        $this->assertEquals(false, $this->config->isFeatureSupported('stopwords', 'non_exist'));
        $this->assertEquals(false, $this->config->isFeatureSupported('non_exist', 'non_exist'));
        $this->assertEquals(false, $this->config->isFeatureSupported('non_exist', 'mysql'));
    }
}
