<?php
/**
 * Locator for page layouts XSD schemas.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Layout\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema;

    /**
     * @param Filesystem $appFilesystem
     */
    public function __construct(Filesystem $appFilesystem)
    {
        $this->_schema = $appFilesystem->getDirectoryRead(DirectoryList::LIB_INTERNAL)->getAbsolutePath()
            . '/Magento/Framework/View/PageLayout/etc/layouts.xsd';
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->_schema;
    }
}
