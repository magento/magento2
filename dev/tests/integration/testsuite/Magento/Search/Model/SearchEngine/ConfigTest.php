<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\SearchEngine;

/**
 * Class ConfigTest
 *
 * @magentoAppIsolation enabled
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        $xmlPath = __DIR__ . '/../../_files/search_engine.xml';
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        // Clear out the clache
        $cacheManager = $objectManager->create(\Magento\Framework\App\Cache\Manager::class);
        /** @var \Magento\Framework\App\Cache\Manager $cacheManager */
        $cacheManager->clean($cacheManager->getAvailableTypes());

        $fileResolver = $this->getMockForAbstractClass(
            \Magento\Framework\Config\FileResolverInterface::class,
            [],
            '',
            false
        );
        $fileResolver->expects($this->any())->method('get')->willReturn([file_get_contents($xmlPath)]);

        $configReader = $objectManager->create(
            \Magento\Framework\Search\SearchEngine\Config\Reader::class,
            ['fileResolver' => $fileResolver]
        );
        $dataStorage = $objectManager->create(
            \Magento\Search\Model\SearchEngine\Config\Data::class,
            ['reader' => $configReader]
        );
        $this->config = $objectManager->create(
            \Magento\Search\Model\SearchEngine\Config::class,
            ['dataStorage' => $dataStorage]
        );
    }

    /**
     * Data provider for the test
     *
     * @return array
     */
    public static function loadGetDeclaredFeaturesDataProvider()
    {
        return [
            'features-synonyms' => [
                'searchEngine' => 'mysql',
                'expectedResult' => ['synonyms']
            ],
            'features-synonyms-stopwords' => [
                'searchEngine' => 'other',
                'expectedResult' => ['synonyms', 'stopwords']
            ],
            'features-none1' => [
                'searchEngine' => 'none1',
                'expectedResult' => []
            ],
            'features-none2' => [
                'searchEngine' => 'none2',
                'expectedResult' => []
            ],
            'features-none_exist' => [
                'searchEngine' => 'none_exist',
                'expectedResult' => []
            ]

        ];
    }

    /**
     * @param string $searchEngine
     * @param array $expectedResult
     * @dataProvider loadGetDeclaredFeaturesDataProvider
     */
    public function testGetDeclaredFeatures($searchEngine, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->config->getDeclaredFeatures($searchEngine));
    }

    /**
     * Data provider for the test
     *
     * @return array
     */
    public static function loadIsFeatureSupportedDataProvider()
    {
        return [
            [
                'feature' => 'synonyms',
                'searchEngine' => 'mysql',
                'expectedResult' => true
            ],
            [
                'feature' => 'stopwords',
                'searchEngine' => 'mysql',
                'expectedResult' => false
            ],
            [
                'feature' => 'synonyms',
                'searchEngine' => 'other',
                'expectedResult' => true
            ],
            [
                'feature' => 'stopwords',
                'searchEngine' => 'other',
                'expectedResult' => true
            ],
            [
                'feature' => 'synonyms',
                'searchEngine' => 'none1',
                'expectedResult' => false
            ],
            [
                'feature' => 'stopwords',
                'searchEngine' => 'none1',
                'expectedResult' => false
            ],
            [
                'feature' => 'synonyms',
                'searchEngine' => 'none2',
                'expectedResult' => false
            ],
            [
                'feature' => 'stopwords',
                'searchEngine' => 'none2',
                'expectedResult' => false
            ],
            [
                'feature' => 'stopwords',
                'searchEngine' => 'none_exist',
                'expectedResult' => false
            ],
            [
                'feature' => 'none_exist',
                'searchEngine' => 'none_exist',
                'expectedResult' => false
            ],
            [
                'feature' => 'none_exist',
                'searchEngine' => 'mysql',
                'expectedResult' => false
            ]
        ];
    }

    /**
     * @param string $searchEngine
     * @param string $feature
     * @param array $expectedResult
     * @dataProvider loadIsFeatureSupportedDataProvider
     */
    public function testIsFeatureSupported($searchEngine, $feature, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->config->isFeatureSupported($searchEngine, $feature));
    }
}
