<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Abstract class for phrase testing
 */
namespace Magento\Test\Integrity\Phrase;

use Magento\Tools\I18n\FilesCollector;

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
     * @return \RegexIterator
     */
    protected function _getFiles()
    {
        $filesCollector = new \Magento\Tools\I18n\FilesCollector();

        return $filesCollector->getFiles(
            [\Magento\Framework\Test\Utility\Files::init()->getPathToSource()],
            '/\.(php|phtml)$/'
        );
    }
}
