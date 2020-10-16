<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\SynonymAnalyzer;
use Magento\Search\Model\SynonymReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SynonymAnalyzerTest extends TestCase
{
    /**
     * @var SynonymAnalyzer
     */
    private $synonymAnalyzer;

    /**
     * @var SynonymReader|MockObject
     */
    private $synReaderModel;

    /**
     * Test set up
     */
    protected function setUp(): void
    {
        $helper = new ObjectManager($this);

        $this->synReaderModel = $this->getMockBuilder(SynonymReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->synonymAnalyzer = $helper->getObject(
            SynonymAnalyzer::class,
            [
                'synReader' => $this->synReaderModel,
            ]
        );
    }

    /**
     * @test
     */
    public function testGetSynonymsForPhrase()
    {
        $phrase = 'Elizabeth/Angela is the british queen';
        $expected = [
            0 => [ 0 => "Elizabeth/Angela" ],
            1 => [ 0 => "is" ],
            2 => [ 0 => "the" ],
            3 => [ 0 => "british", 1 => "english" ],
            4 => [ 0 => "queen", 1 => "monarch" ],
        ];
        $this->synReaderModel->expects($this->once())
            ->method('loadByPhrase')
            ->with($phrase)
            ->willReturnSelf();
        $this->synReaderModel->expects($this->once())
            ->method('getData')
            ->willReturn([
                ['synonyms' => 'british,english'],
                ['synonyms' => 'queen,monarch'],
            ]);

        $actual = $this->synonymAnalyzer->getSynonymsForPhrase($phrase);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     *
     * Empty phrase scenario
     */
    public function testGetSynonymsForPhraseEmptyPhrase()
    {
        $phrase = '';
        $expected = [];
        $actual = $this->synonymAnalyzer->getSynonymsForPhrase($phrase);
        $this->assertEquals($expected, $actual);
    }
}
