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
     * Covers
     * $.mage.__('Example text')
     */
    public const REGEX_MAGE_TRANSLATE = '/mage\.__\(\s*([\'"])(.*?[^\\\])\1.*?[),]/';

    /**
     * Covers in JS
     * $t(' Example: ')
     *
     * Covers in HTML
     * <a data-bind="attr: { title: $t('Title'), href: '#'} "></a>
     * <input type="text" data-bind="attr: { placeholder: $t('Placeholder'), title: $t('Title') }" />
     * Double quotes are not handled correctly in the `attr` binding. Move phrase to the UI component property if needed
     */
    public const REGEX_TRANSLATE_FUNCTION = '/\\$t\(\s*([\'"])(.*?[^\\\])\1.*?[),]/';

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
            $regexes = [
                static::REGEX_MAGE_TRANSLATE,
                static::REGEX_TRANSLATE_FUNCTION
            ];

            foreach ($regexes as $regex) {
                preg_match_all($regex, $fileRow, $results, PREG_SET_ORDER);
                for ($i = 0, $count = count($results); $i < $count; $i++) {
                    if (isset($results[$i][2])) {
                        $quote = $results[$i][1];
                        $this->_addPhrase($quote . $results[$i][2] . $quote, $lineNumber);
                    }
                }
            }
        }
        fclose($fileHandle);
    }
}
