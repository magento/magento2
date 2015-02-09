<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Scan source code for detects invocations of outdated __() method
 */
namespace Magento\Test\Integrity\Phrase\Legacy;

use Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer;
use Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\Translate\MethodCollector;

class SignatureTest extends \Magento\Test\Integrity\Phrase\AbstractTestCase
{
    /**
     * @var \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\Translate\MethodCollector
     */
    protected $_phraseCollector;

    protected function setUp()
    {
        $this->_phraseCollector = new \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\Translate\MethodCollector(
            new \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer()
        );
    }

    public function testSignature()
    {
        $errors = [];
        foreach ($this->_getFiles() as $file) {
            $this->_phraseCollector->parse($file);
            foreach ($this->_phraseCollector->getPhrases() as $phrase) {
                $errors[] = $this->_createPhraseError($phrase);
            }
        }
        $this->assertEmpty(
            $errors,
            sprintf(
                '%d usages of the old translation method call were discovered: %s',
                count($errors),
                implode("\n\n", $errors)
            )
        );
    }
}
