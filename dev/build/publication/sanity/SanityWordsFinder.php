<?php
/**
 * Service routines for sanity check command line script
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   build
 * @package    sanity
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Extend words finder class, which is designed for sanity tests. The added functionality is method to search through
 * directories and method to return words list for logging.
 */
class SanityWordsFinder extends Inspection_WordsFinder
{
    /**
     * Get list of words, configured to be searched
     *
     * @return array
     */
    public function getSearchedWords()
    {
        return $this->_words;
    }

    /**
     * Searche words in files content recursively within base directory tree
     *
     * @return array
     */
    public function findWordsRecursively()
    {
        return $this->_findWordsRecursively($this->_baseDir);
    }

    /**
     * Search words in files content recursively within base directory tree
     *
     * @param  string $currentDir Current dir to look in
     * @return array
     */
    protected function _findWordsRecursively($currentDir)
    {
        $result = array();

        $entries = glob($currentDir . DIRECTORY_SEPARATOR . '*');
        $initialLength = strlen($this->_baseDir);
        foreach ($entries as $entry) {
            if (is_file($entry)) {
                $foundWords = $this->findWords($entry);
                if (!$foundWords) {
                    continue;
                }
                $relPath = substr($entry, $initialLength + 1);
                $result[] = array('words' => $foundWords, 'file' => $relPath);
            } else if (is_dir($entry)) {
                $more = $this->_findWordsRecursively($entry);
                $result = array_merge($result, $more);
            }
        }

        return $result;
    }
}
