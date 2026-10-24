<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Adapter\Query\Preprocessor;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Adapter\Query\Preprocessor\Synonyms;
use Magento\Search\Api\SynonymAnalyzerInterface;
use Magento\Search\Model\SynonymAnalyzer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SynonymsTest extends TestCase
{
    /**
     * @var SynonymAnalyzerInterface|MockObject
     */
    private $synonymAnalyzer;

    /**
     * @var Synonyms
     */
    private $synonymPreprocessor;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->synonymAnalyzer = $this->getMockBuilder(SynonymAnalyzer::class)
            ->onlyMethods(['getSynonymsForPhrase'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->synonymPreprocessor = $objectManager->getObject(
            Synonyms::class,
            [
                'synonymsAnalyzer' => $this->synonymAnalyzer
            ]
        );
    }

    /**
     * Data provider for the test
     *
     * @return array
     */
    public static function getDataProvider()
    {
        return [
            'oneWord' => [
                'big',
                [['big', 'huge']],
                'big huge'
            ],
            'twoWords' => [
                'big universe',
                [['big', 'huge'], ['universe', 'cosmos']],
                'big huge universe cosmos'
            ],
            'noSynonyms' => [
                'no synonyms',
                [['no'], ['synonyms']],
                'no synonyms'
            ]
        ];
    }

    #[DataProvider('getDataProvider')]
    public function testProcess($query, $result, $newQuery)
    {
        $this->synonymAnalyzer->expects($this->once())
            ->method('getSynonymsForPhrase')
            ->with($query)
            ->willReturn($result);

        $actualResult = $this->synonymPreprocessor->process($query);
        $this->assertEquals($newQuery, $actualResult);
    }
}
