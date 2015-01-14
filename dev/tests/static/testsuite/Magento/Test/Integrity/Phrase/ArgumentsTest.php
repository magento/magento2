<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Scan source code for detects invocations of __() function, analyzes placeholders with arguments
 * and see if they not equal
 */
namespace Magento\Test\Integrity\Phrase;

use Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer;

class ArgumentsTest extends \Magento\Test\Integrity\Phrase\AbstractTestCase
{
    /**
     * @var \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector
     */
    protected $_phraseCollector;

    /**
     * List of files that must be omitted
     *
     * @todo remove blacklist related logic when all files correspond to the standard
     * @var array
     */
    protected $blackList;

    protected function setUp()
    {
        $this->_phraseCollector = new \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector(
            new \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer()
        );

        $rootDir = \Magento\Framework\Test\Utility\Files::init()->getPathToSource();
        $this->blackList = [
            // the file below is the only file where strings are translated without corresponding arguments
            $rootDir . '/app/code/Magento/Translation/Model/Js/DataProvider.php',
        ];
    }

    public function testArguments()
    {
        $errors = [];
        foreach ($this->_getFiles() as $file) {
            if (in_array($file, $this->blackList)) {
                continue;
            }
            $this->_phraseCollector->parse($file);
            foreach ($this->_phraseCollector->getPhrases() as $phrase) {
                if (preg_match_all('/%(\d+)/', $phrase['phrase'], $matches) || $phrase['arguments']) {
                    $placeholdersInPhrase = array_unique($matches[1]);
                    if (count($placeholdersInPhrase) != $phrase['arguments']) {
                        $errors[] = $this->_createPhraseError($phrase);
                    }
                }
            }
        }
        $this->assertEmpty(
            $errors,
            sprintf(
                "\n%d usages of inconsistency the number of arguments and placeholders were discovered: \n%s",
                count($errors),
                implode("\n\n", $errors)
            )
        );
    }
}
