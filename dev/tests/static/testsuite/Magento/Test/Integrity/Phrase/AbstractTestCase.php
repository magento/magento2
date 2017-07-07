<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract class for phrase testing
 */
namespace Magento\Test\Integrity\Phrase;

use Magento\Framework\Component\ComponentRegistrar;
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
        $filesCollector = new FilesCollector();
        $componentRegistrar = new ComponentRegistrar();
        $paths = array_merge(
            $componentRegistrar->getPaths(ComponentRegistrar::MODULE),
            $componentRegistrar->getPaths(ComponentRegistrar::LIBRARY)
        );
        return $filesCollector->getFiles(
            $paths,
            '/\.(php|phtml)$/'
        );
    }
}
