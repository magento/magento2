<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class SynonymAnalyzerTest
 */
class SynonymAnalyzerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Search\Model\SynonymAnalyzer
     */
    private $synonymAnalyzer;

    /**
     * @var \Magento\Search\Model\SynonymReader |\PHPUnit_Framework_MockObject_MockObject
     */
    private $synReaderModel;

    /**
     * Test set up
     */
    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->synReaderModel = $this->getMockBuilder(\Magento\Search\Model\SynonymReader::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->synonymAnalyzer = $helper->getObject(
            \Magento\Search\Model\SynonymAnalyzer::class,
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
        $phrase = 'Elizabeth is the british queen';
        $expected = [
            0 => [ 0 => "Elizabeth" ],
            1 => [ 0 => "is" ],
            2 => [ 0 => "the" ],
            3 => [ 0 => "british", 1 => "english" ],
            4 => [ 0 => "queen", 1 => "monarch" ],
        ];
        $this->synReaderModel->expects($this->once())
            ->method('loadByPhrase')
            ->with($phrase)
            ->willReturnSelf()
        ;
        $this->synReaderModel->expects($this->once())
            ->method('getData')
            ->willReturn([
                ['synonyms' => 'british,english'],
                ['synonyms' => 'queen,monarch'],
            ])
        ;

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
