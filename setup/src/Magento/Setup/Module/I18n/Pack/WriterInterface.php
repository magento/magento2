<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Pack;

use Magento\Setup\Module\I18n\Dictionary;
use Magento\Setup\Module\I18n\Locale;

/**
 * Pack writer interface
 */
interface WriterInterface
{
    /**#@+
     * Save pack modes
     */
    const MODE_REPLACE = 'replace';

    const MODE_MERGE = 'merge';

    /**#@-*/

    /**
     * Write dictionary data to language pack
     *
     * @param \Magento\Setup\Module\I18n\Dictionary $dictionary
     * @param string $packPath
     * @param \Magento\Setup\Module\I18n\Locale $locale
     * @param string $mode One of const of WriterInterface::MODE_
     * @return void
     * @deprecated Writing to a specified pack path is not supported after custom vendor directory support.
     * Dictionary data will be written to current Magento codebase.
     */
    public function write(Dictionary $dictionary, $packPath, Locale $locale, $mode);

    /**
     * Write dictionary data to current Magento codebase
     *
     * @param \Magento\Setup\Module\I18n\Dictionary $dictionary
     * @param \Magento\Setup\Module\I18n\Locale $locale
     * @param string $mode One of const of WriterInterface::MODE_
     * @return void
     */
    public function writeDictionary(Dictionary $dictionary, Locale $locale, $mode);
}
