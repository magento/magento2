<?php
/**
 * Logging schema locator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Initial;

use Magento\Framework\Module\Dir;

/**
 * Class \Magento\Framework\App\Config\Initial\SchemaLocator
 *
 * @since 2.0.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for config
     *
     * @var string
     * @since 2.0.0
     */
    protected $_schema = null;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param string $moduleName
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader, $moduleName)
    {
        $this->_schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, $moduleName) . '/config.xsd';
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPerFileSchema()
    {
        return $this->_schema;
    }
}
