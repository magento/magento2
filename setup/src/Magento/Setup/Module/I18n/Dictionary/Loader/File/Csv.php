<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Module\I18n\Dictionary\Loader\File;

use Magento\Setup\Module\I18n\Dictionary;

/**
 *  Dictionary loader from csv
 */
class Csv extends AbstractFile
{
    /**
     * {@inheritdoc}
     */
    protected function _readFile()
    {
        return fgetcsv($this->_fileHandler, null, ',', '"', '\\');
    }
}
