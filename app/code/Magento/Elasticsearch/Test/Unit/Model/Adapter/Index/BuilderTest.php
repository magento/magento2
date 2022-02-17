<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\Index;

use Magento\Elasticsearch\Model\Adapter\Index\Builder;
use Magento\Elasticsearch\Model\Adapter\Index\Config\EsConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Search\Model\ResourceModel\SynonymReader;
use Magento\Framework\DB\Adapter\AdapterInterface;
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
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

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

        $this->connectionMock = $this->getMockBuilder(
            AdapterInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->selectMock = $this->getMockBuilder(
            Select::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->synonymReaderMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->expects($this->once())
            ->method('from')
            ->willReturn($this->selectMock);

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

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
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
        $this->assertNotContains($synonymsFilterName,
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
     * Test build() method with synonyms provided.
     *
     * In this case synonyms filter should be created, populated with the list of available synonyms
     * and referenced in the prefix_search and sku_prefix_search analyzers.
     *
     * @param string $locale
     * @dataProvider buildDataProvider
     */
    public function testBuildWithProvidedSynonyms(string $locale)
    {
        $synonymsFilterName = 'synonyms';

        $synonyms = [
            'mp3,player,sound,audio',
            'tv,video,television,screen'
        ];

        $expectedFilter = [
                'type' => 'synonym_graph',
                'synonyms' => $synonyms
        ];

        $this->localeResolver->expects($this->once())
            ->method('getLocale')
            ->willReturn($locale);

        $this->connectionMock->expects($this->once())
            ->method('fetchCol')
            ->willReturn($synonyms);

        $result = $this->model->build();

        $analysisFilters = $result["analysis"]["filter"];
        $prefixSearchAnalyzerFilters = $result["analysis"]["analyzer"]["prefix_search"]["filter"];
        $skuPrefixSearchAnalyzerFilters = $result["analysis"]["analyzer"]["sku_prefix_search"]["filter"];

        $this->assertArrayHasKey(
            $synonymsFilterName,
            $analysisFilters,
            'Analysis filters must contain synonyms when defined'
        );
        $this->assertContains(
            $expectedFilter,
            $analysisFilters,
            'Analysis synonyms filter must match the expected result'
        );
        $this->assertContains($synonymsFilterName,
            $prefixSearchAnalyzerFilters,
            'The prefix_search analyzer must include synonyms filter'
        );
        $this->assertContains($synonymsFilterName,
            $skuPrefixSearchAnalyzerFilters,
            'The sku_prefix_search analyzer must include synonyms filter'
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
