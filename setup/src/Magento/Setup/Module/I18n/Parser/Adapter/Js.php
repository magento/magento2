<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter;

/**
 * Js parser adapter
 */
class Js extends AbstractAdapter
{
    /**
     * @inheritdoc
     */
    protected function _parse()
    {
        $fileHandle = @fopen($this->_file, 'r');
        $lineNumber = 0;
        while (!feof($fileHandle)) {
            $lineNumber++;
            $fileRow = fgets($fileHandle, 4096);
            $results = [];
            preg_match_all('/mage\.__\(\s*([\'"])(.*?[^\\\])\1.*?[),]/', $fileRow, $results, PREG_SET_ORDER);
<<<<<<< HEAD
            $resultsCount = count($results);
            for ($i = 0; $i < $resultsCount; $i++) {
=======
            for ($i = 0, $count = count($results); $i < $count; $i++) {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                if (isset($results[$i][2])) {
                    $quote = $results[$i][1];
                    $this->_addPhrase($quote . $results[$i][2] . $quote, $lineNumber);
                }
            }

            preg_match_all('/\\$t\(\s*([\'"])(.*?[^\\\])\1.*?[),]/', $fileRow, $results, PREG_SET_ORDER);
<<<<<<< HEAD
            $resultsCount = count($results);
            for ($i = 0; $i < $resultsCount; $i++) {
=======
            for ($i = 0, $count = count($results); $i < $count; $i++) {
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                if (isset($results[$i][2])) {
                    $quote = $results[$i][1];
                    $this->_addPhrase($quote . $results[$i][2] . $quote, $lineNumber);
                }
            }
        }
        fclose($fileHandle);
    }
}
