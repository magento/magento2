<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary\Loader\File;

use Magento\Setup\Module\I18n\Dictionary;

/**
 *  Dictionary loader from csv
 * @since 2.0.0
 */
class Csv extends AbstractFile
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    protected function _readFile()
    {
        return fgetcsv($this->_fileHandler, null, ',', '"');
    }
}
