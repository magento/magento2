<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Parser\Adapter;

class PhpTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|
     * \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector
     */
    protected $_phraseCollectorMock;

    /**
     * @var \Magento\Tools\I18n\Parser\Adapter\Php
     */
    protected $_adapter;

    protected function setUp()
    {
        $this->_phraseCollectorMock = $this->getMock(
            'Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector',
            [],
            [],
            '',
            false
        );

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_adapter = $objectManagerHelper->getObject(
            'Magento\Tools\I18n\Parser\Adapter\Php',
            ['phraseCollector' => $this->_phraseCollectorMock]
        );
    }

    public function testParse()
    {
        $expectedResult = [['phrase' => 'phrase1', 'file' => 'file1', 'line' => 15, 'quote' => '']];

        $this->_phraseCollectorMock->expects($this->once())->method('parse')->with('file1');
        $this->_phraseCollectorMock->expects(
            $this->once()
        )->method(
            'getPhrases'
        )->will(
            $this->returnValue([['phrase' => 'phrase1', 'file' => 'file1', 'line' => 15]])
        );

        $this->_adapter->parse('file1');
        $this->assertEquals($expectedResult, $this->_adapter->getPhrases());
    }
}
