<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Config\Consolidated;

use Magento\Framework\Module\Dir;

/**
 * Integration config schema locator.
 * @since 2.1.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     * @since 2.1.0
     */
    protected $_schema = null;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     * @since 2.1.0
     */
    protected $_perFileSchema = null;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @since 2.1.0
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $etcDir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Integration');
        $this->_schema = $etcDir . '/integration/integration.xsd';
        $this->_perFileSchema = $etcDir . '/integration/integration_file.xsd';
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     * @since 2.1.0
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     * @since 2.1.0
     */
    public function getPerFileSchema()
    {
        return $this->_perFileSchema;
    }
}
