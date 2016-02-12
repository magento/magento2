<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;

class Currency extends \Zend_Currency implements CurrencyInterface
{
    /**
     * Creates a currency instance.
     *
     * @param Filesystem $filesystem
     * @param  string|array       $options OPTIONAL Options array or currency short name
     *                                              when string is given
     * @param  string|\Zend_Locale $locale  OPTIONAL locale name
     * @throws \Zend_Currency_Exception When currency is invalid
     */
    public function __construct(
        Filesystem $filesystem,
        $options = null,
        $locale = null
    ) {
        // create zend cache in cache directory and set files to 0660 permissions
        $cache = \Zend_Cache::factory(
            'Core',
            'File',
            ['automatic_serialization' => true],
            [
                'cache_dir' => $filesystem->getDirectoryWrite(DirectoryList::CACHE)->getAbsolutePath(),
                'cache_file_perm' => DriverInterface::WRITEABLE_FILE_MODE,
            ]
        );
        \Zend_Currency::setCache($cache);
        parent::__construct($options, $locale);
    }
}
