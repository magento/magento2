<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n;

class DictionaryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Dictionary
     */
    protected $_dictionary;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_dictionary = $objectManagerHelper->getObject('Magento\Setup\Module\I18n\Dictionary');
    }

    public function testPhraseCollecting()
    {
        $phraseFirstMock = $this->getMock('Magento\Setup\Module\I18n\Dictionary\Phrase', [], [], '', false);
        $phraseSecondMock = $this->getMock('Magento\Setup\Module\I18n\Dictionary\Phrase', [], [], '', false);

        $this->_dictionary->addPhrase($phraseFirstMock);
        $this->_dictionary->addPhrase($phraseSecondMock);

        $this->assertEquals([$phraseFirstMock, $phraseSecondMock], $this->_dictionary->getPhrases());
    }

    public function testGetDuplicates()
    {
        $phraseFirstMock = $this->getMock('Magento\Setup\Module\I18n\Dictionary\Phrase', [], [], '', false);
        $phraseFirstMock->expects($this->once())->method('getKey')->will($this->returnValue('key_1'));
        $phraseSecondMock = $this->getMock('Magento\Setup\Module\I18n\Dictionary\Phrase', [], [], '', false);
        $phraseSecondMock->expects($this->once())->method('getKey')->will($this->returnValue('key_1'));
        $phraseThirdMock = $this->getMock('Magento\Setup\Module\I18n\Dictionary\Phrase', [], [], '', false);
        $phraseThirdMock->expects($this->once())->method('getKey')->will($this->returnValue('key_3'));

        $this->_dictionary->addPhrase($phraseFirstMock);
        $this->_dictionary->addPhrase($phraseSecondMock);
        $this->_dictionary->addPhrase($phraseThirdMock);

        $this->assertEquals([[$phraseFirstMock, $phraseSecondMock]], $this->_dictionary->getDuplicates());
    }
}
