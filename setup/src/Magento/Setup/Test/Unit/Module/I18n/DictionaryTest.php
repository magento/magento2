<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\Dictionary;
use Magento\Setup\Module\I18n\Dictionary\Phrase;
use PHPUnit\Framework\TestCase;

class DictionaryTest extends TestCase
{
    /**
     * @var Dictionary
     */
    protected $_dictionary;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->_dictionary = $objectManagerHelper->getObject(Dictionary::class);
    }

    public function testPhraseCollecting()
    {
        $phraseFirstMock = $this->createMock(Phrase::class);
        $phraseSecondMock = $this->createMock(Phrase::class);

        $this->_dictionary->addPhrase($phraseFirstMock);
        $this->_dictionary->addPhrase($phraseSecondMock);

        $this->assertEquals([$phraseFirstMock, $phraseSecondMock], $this->_dictionary->getPhrases());
    }

    public function testGetDuplicates()
    {
        $phraseFirstMock = $this->createMock(Phrase::class);
        $phraseFirstMock->expects($this->once())->method('getKey')->willReturn('key_1');
        $phraseSecondMock = $this->createMock(Phrase::class);
        $phraseSecondMock->expects($this->once())->method('getKey')->willReturn('key_1');
        $phraseThirdMock = $this->createMock(Phrase::class);
        $phraseThirdMock->expects($this->once())->method('getKey')->willReturn('key_3');

        $this->_dictionary->addPhrase($phraseFirstMock);
        $this->_dictionary->addPhrase($phraseSecondMock);
        $this->_dictionary->addPhrase($phraseThirdMock);

        $this->assertEquals([[$phraseFirstMock, $phraseSecondMock]], $this->_dictionary->getDuplicates());
    }
}
