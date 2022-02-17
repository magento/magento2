<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Adapter\Index\Builder;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Search\Model\ResourceModel\SynonymReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    private $model;

    /**
     * @var LocaleResolver|MockObject
     */
    private $localeResolver;

    /**
     * @var EsConfigInterface|MockObject
     */
    private $esConfig;

    /**
     * @var SynonymReader|MockObject
     */
    private $synonymReaderMock;

    /**
     * Setup method
     * @return void
     */
    protected function setUp(): void
    {
        $this->localeResolver = $this->getMockBuilder(\Magento\Framework\Locale\Resolver::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'emulate',
                'getLocale'
            ])
            ->getMock();

        $this->esConfig = $this->getMockBuilder(
            EsConfigInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->synonymReaderMock = $this->getMockBuilder(
            SynonymReader::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->esConfig->expects($this->once())
            ->method('getStemmerInfo')
            ->willReturn([
                'type' => 'stemmer',
                'default' => 'english',
                'en_US' => 'english',
            ]);

        $objectManager = new ObjectManagerHelper($this);
        $this->model = $objectManager->getObject(
            Builder::class,
            [
                'localeResolver' => $this->localeResolver,
                'esConfig' => $this->esConfig,
                'synonymReader' => $this->synonymReaderMock
            ]
        );
    }

    /**
     * Test build() method without provided synonyms.
     *
     * In this case, synonyms filter must not be created or referenced
     * in the prefix_search and sku_prefix_search analyzers.
     *
     * @param string $locale
     * @dataProvider buildDataProvider
     */
    public function testBuildWithoutSynonymsProvided(string $locale)
    {
        $synonymsFilterName = 'synonyms';

        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn($locale);
        $this->synonymReaderMock->expects($this->once())
            ->method('getSynonymFilter')
            ->willReturn([]);

        $result = $this->model->build();

        $analysisFilters = $result["analysis"]["filter"];
        $prefixSearchAnalyzerFilters = $result["analysis"]["analyzer"]["prefix_search"]["filter"];
        $skuPrefixSearchAnalyzerFilters = $result["analysis"]["analyzer"]["sku_prefix_search"]["filter"];

        $this->assertArrayNotHasKey(
            $synonymsFilterName,
            $analysisFilters,
            'Analysis filters must not contain synonyms when they are not defined'
        );
        $this->assertNotContains(
            $synonymsFilterName,
            $prefixSearchAnalyzerFilters,
            'The prefix_search analyzer must not include synonyms filter when it is not present'
        );
        $this->assertNotContains(
            $synonymsFilterName,
            $skuPrefixSearchAnalyzerFilters,
            'The sku_prefix_search analyzer must include synonyms filter when it is not present'
        );
    }

    /**
     * @return array
     */
    public function buildDataProvider()
    {
        return [
            ['en_US'],
            ['de_DE'],
        ];
    }
}
