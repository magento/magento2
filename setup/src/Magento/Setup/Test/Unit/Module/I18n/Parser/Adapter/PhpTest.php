<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter;

class PhpTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|
     * \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector
     */
    protected $_phraseCollectorMock;

    /**
     * @var \Magento\Setup\Module\I18n\Parser\Adapter\Php
     */
    protected $_adapter;

    protected function setUp(): void
    {
        $this->_phraseCollectorMock =
            $this->createMock(\Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_adapter = $objectManagerHelper->getObject(
            \Magento\Setup\Module\I18n\Parser\Adapter\Php::class,
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
        )->willReturn(
            [['phrase' => 'phrase1', 'file' => 'file1', 'line' => 15]]
        );

        $this->_adapter->parse('file1');
        $this->assertEquals($expectedResult, $this->_adapter->getPhrases());
    }
}
