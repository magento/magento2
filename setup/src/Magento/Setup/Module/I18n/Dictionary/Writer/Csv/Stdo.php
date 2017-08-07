<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

    /**
     * Overriding parent as we can not close globally used resource
     *
     * @return void
     * @since 2.2.0
     */
    public function __destruct()
    {
    }
}
