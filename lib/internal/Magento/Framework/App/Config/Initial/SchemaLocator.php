<?php
/**
 * Logging schema locator
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Initial;

use Magento\Framework\Module\Dir;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for config
     *
     * @var string
     */
    protected $_schema = null;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param string $moduleName
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader, $moduleName)
    {
        $this->_schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, $moduleName) . '/config.xsd';
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
        return $this->_schema;
    }
}
