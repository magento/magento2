<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Test\Integrity\Phrase;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * Scan source code for detects invocations of __() function or Phrase object, analyzes placeholders with arguments
 * and see if they not equal
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

    protected function setUp(): void
    {
        $this->_phraseCollector = new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector(
            new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer(),
            true,
            \Magento\Framework\Phrase::class
        );

        $componentRegistrar = new ComponentRegistrar();
        $this->blackList = [
            // the file below is the only file where strings are translated without corresponding arguments
            $componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Magento_Translation')
                . '/Model/Js/DataProvider.php',
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
                $this->checkEmptyPhrases($phrase, $missedPhraseErrors);
                $this->checkArgumentMismatch($phrase, $incorrectNumberOfArgumentsErrors);
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

    /**
     * Will check if phrase is empty
     *
     * @param $phrase
     * @param $missedPhraseErrors
     */
    private function checkEmptyPhrases($phrase, &$missedPhraseErrors)
    {
        if (empty(trim($phrase['phrase'], "'\"\t\n\r\0\x0B"))) {
            $missedPhraseErrors[] = $this->_createMissedPhraseError($phrase);
        }
    }

    /**
     * Will check if the number of arguments does not match the number of placeholders
     *
     * @param $phrase
     * @param $incorrectNumberOfArgumentsErrors
     */
    private function checkArgumentMismatch($phrase, &$incorrectNumberOfArgumentsErrors)
    {
        if (preg_match_all('/%(\w+)/', $phrase['phrase'], $matches) || $phrase['arguments']) {
            $placeholderCount = count(array_unique($matches[1]));

            // Check for zend placeholders %placeholder% and sprintf placeholder %s
            if (preg_match_all('/%((s)|([A-Za-z]+)%)/', $phrase['phrase'], $placeHolders, PREG_OFFSET_CAPTURE)) {
                foreach ($placeHolders[0] as $ph) {
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
