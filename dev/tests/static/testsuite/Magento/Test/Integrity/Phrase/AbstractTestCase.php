<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract class for phrase testing
 */
namespace Magento\Test\Integrity\Phrase;

use Magento\Setup\Module\I18n\FilesCollector;

class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $phrase
     * @return string
     */
    protected function _createPhraseError($phrase)
    {
        return "\nPhrase: {$phrase['phrase']} \nFile: {$phrase['file']} \nLine: {$phrase['line']}";
    }

    /**
     * @param array $phrase
     * @return string
     */
    protected function _createMissedPhraseError($phrase)
    {
        return "\nMissed Phrase: File: {$phrase['file']} \nLine: {$phrase['line']}";
    }

    /**
     * @return \RegexIterator
     */
    protected function _getFiles()
    {
        $filesCollector = new \Magento\Setup\Module\I18n\FilesCollector();

        return $filesCollector->getFiles(
            [\Magento\Framework\App\Utility\Files::init()->getPathToSource()],
            '/\.(php|phtml)$/'
        );
    }
}
