<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Scan source code for detects invocations of outdated __() method
 */
namespace Magento\Test\Integrity\Phrase\Legacy;

use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Translate\MethodCollector;

class SignatureTest extends \Magento\Test\Integrity\Phrase\AbstractTestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\Translate\MethodCollector
     */
    protected $_phraseCollector;

    protected function setUp(): void
    {
        $this->_phraseCollector = new MethodCollector(
            new Tokenizer()
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
