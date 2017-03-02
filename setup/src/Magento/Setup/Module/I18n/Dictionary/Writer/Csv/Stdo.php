<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary\Writer\Csv;

use Magento\Setup\Module\I18n\Dictionary\Writer\Csv;

/**
 * Stdout writer
 *
 * Output csv format to stdout
 */
class Stdo extends Csv
{
    /**
     * Writer construct
     */
    public function __construct()
    {
        $this->_fileHandler = STDOUT;
    }
}
