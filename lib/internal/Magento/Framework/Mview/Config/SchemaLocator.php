<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema = null;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $_perFileSchema = null;

    /**
     * @param \Magento\Framework\Filesystem\DirectoryList $directoryList
     */
    public function __construct(\Magento\Framework\Filesystem\DirectoryList $directoryList)
    {
        $etcDir = $directoryList->getPath(DirectoryList::LIB_INTERNAL)
            . '/Magento/Framework/Mview/etc';
        $this->_schema = $etcDir . '/mview.xsd';
        $this->_perFileSchema = $etcDir . '/mview.xsd';
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
     * Get path to pre file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->_perFileSchema;
    }
}
