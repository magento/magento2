<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity\Phrase;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer;

/**
 * Scan source code for detects invocations of __() function or Phrase object, analyzes placeholders with arguments
 * and see if they not equal
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class ArgumentsTest extends \Magento\Test\Integrity\Phrase\AbstractTestCase
{
    /**
     * @var \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector
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
        $this->_phraseCollector = new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector(
            new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer(),
            true,
            'Magento\Framework\Phrase'
        );

        $componentRegistrar = new ComponentRegistrar();
        $this->blackList = [
            // the file below is the only file where strings are translated without corresponding arguments
            $componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Magento_Translation')
                . '/Model/Js/DataProvider.php',
            $componentRegistrar->getPath(ComponentRegistrar::MODULE, '')
        ];
    }

    public function testArguments()
    {
        $incorrectNumberOfArgumentsErrors = [];
        $missedPhraseErrors = [];
        foreach ($this->_getFiles() as $file) {
            if (in_array($file, $this->blackList)) {
                continue;
            }
            $this->_phraseCollector->parse($file);
            foreach ($this->_phraseCollector->getPhrases() as $phrase) {
                if (empty(trim($phrase['phrase'], "'\"\t\n\r\0\x0B"))) {
                    $missedPhraseErrors[] = $this->_createMissedPhraseError($phrase);
                }
                if (preg_match_all('/%(\w+)/', $phrase['phrase'], $matches) || $phrase['arguments']) {
                    $placeholderCount = count(array_unique($matches[1]));

                    if ($placeholderCount != $phrase['arguments']) {
                        // Check for zend placeholders %placehoder% and sprintf placeholder %s
                        if (preg_match_all('/(%(s)|(\w+)%)/', $phrase['phrase'], $placeHlders, PREG_OFFSET_CAPTURE)) {

                            foreach ($placeHlders[0] as $ph) {
                                // Check if char after placeholder is not a digit or letter
                                $charAfterPh = $phrase['phrase'][$ph[1] + strlen($ph[0])];
                                if (!preg_match('/[A-Za-z0-9]/', $charAfterPh)) {
                                    $placeholderCount--;
                                }
                            }
                        }

                        if ($placeholderCount != $phrase['arguments']) {
                            $incorrectNumberOfArgumentsErrors[] = $this->_createPhraseError($phrase);
                        }
                    }
                }
            }
        }
        $this->assertEmpty(
            $missedPhraseErrors,
            sprintf(
                "\n%d missed phrases were discovered: \n%s",
                count($missedPhraseErrors),
                implode("\n\n", $missedPhraseErrors)
            )
        );
        $this->assertEmpty(
            $incorrectNumberOfArgumentsErrors,
            sprintf(
                "\n%d usages of inconsistency the number of arguments and placeholders were discovered: \n%s",
                count($incorrectNumberOfArgumentsErrors),
                implode("\n\n", $incorrectNumberOfArgumentsErrors)
            )
        );
    }
}
