<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter;

/**
 * Js parser adapter
 */
class Js extends AbstractAdapter
{
    /**
     * {@inheritdoc}
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
            for ($i = 0; $i < count($results); $i++) {
                if (isset($results[$i][2])) {
                    $quote = $results[$i][1];
                    $this->_addPhrase($quote . $results[$i][2] . $quote, $lineNumber);
                }
            }
        }
        fclose($fileHandle);
    }
}
