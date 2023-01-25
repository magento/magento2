<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Parser\Adapter;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Parser\Adapter\Php;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector;
use PHPUnit\Framework\TestCase;

class PhpTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|
     * \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector
     */
    protected $_phraseCollectorMock;

    /**
     * @var Php
     */
    protected $_adapter;

    protected function setUp(): void
    {
        $this->_phraseCollectorMock =
            $this->createMock(PhraseCollector::class);

        $objectManagerHelper = new ObjectManager($this);
        $this->_adapter = $objectManagerHelper->getObject(
            Php::class,
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
