<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n;

class DictionaryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Dictionary
     */
    protected $_dictionary;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_dictionary = $objectManagerHelper->getObject(\Magento\Setup\Module\I18n\Dictionary::class);
    }

    public function testPhraseCollecting()
    {
        $phraseFirstMock = $this->createMock(\Magento\Setup\Module\I18n\Dictionary\Phrase::class);
        $phraseSecondMock = $this->createMock(\Magento\Setup\Module\I18n\Dictionary\Phrase::class);

        $this->_dictionary->addPhrase($phraseFirstMock);
        $this->_dictionary->addPhrase($phraseSecondMock);

        $this->assertEquals([$phraseFirstMock, $phraseSecondMock], $this->_dictionary->getPhrases());
    }

    public function testGetDuplicates()
    {
        $phraseFirstMock = $this->createMock(\Magento\Setup\Module\I18n\Dictionary\Phrase::class);
        $phraseFirstMock->expects($this->once())->method('getKey')->will($this->returnValue('key_1'));
        $phraseSecondMock = $this->createMock(\Magento\Setup\Module\I18n\Dictionary\Phrase::class);
        $phraseSecondMock->expects($this->once())->method('getKey')->will($this->returnValue('key_1'));
        $phraseThirdMock = $this->createMock(\Magento\Setup\Module\I18n\Dictionary\Phrase::class);
        $phraseThirdMock->expects($this->once())->method('getKey')->will($this->returnValue('key_3'));

        $this->_dictionary->addPhrase($phraseFirstMock);
        $this->_dictionary->addPhrase($phraseSecondMock);
        $this->_dictionary->addPhrase($phraseThirdMock);

        $this->assertEquals([[$phraseFirstMock, $phraseSecondMock]], $this->_dictionary->getDuplicates());
    }
}
