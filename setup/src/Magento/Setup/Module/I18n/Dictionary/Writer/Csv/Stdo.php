<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
     */
    public function __destruct()
    {
    }
}
