<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Adapter\Query\Preprocessor;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Adapter\Query\Preprocessor\Synonyms;
use Magento\Search\Api\SynonymAnalyzerInterface;
use Magento\Search\Model\SynonymAnalyzer;
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
            ->setMethods(['getSynonymsForPhrase'])
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
    public static function loadProcessDataProvider()
    {
        return [
            'oneWord' => [
                'query' => 'big',
                'result' => [['big', 'huge']],
                'newQuery' => 'big huge'
            ],
            'twoWords' => [
                'query' => 'big universe',
                'result' => [['big', 'huge'], ['universe', 'cosmos']],
                'newQuery' => 'big huge universe cosmos'
            ],
            'noSynonyms' => [
                'query' => 'no synonyms',
                'result' => [['no'], ['synonyms']],
                'newQuery' => 'no synonyms'
            ]
        ];
    }

    /**
     * @param string $phrase
     * @param array $expectedResult
     * @dataProvider loadProcessDataProvider
     */
    public function testProcess($query, $result, $newQuery)
    {
        $this->synonymAnalyzer->expects($this->once())
            ->method('getSynonymsForPhrase')
            ->with($query)
            ->willReturn($result);

        $result = $this->synonymPreprocessor->process($query);
        $this->assertEquals($result, $newQuery);
    }
}
