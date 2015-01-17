<?php
/**
 * Locator for payment config XSD schemas.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Config;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Merged config schema file name
     */
    const MERGED_CONFIG_SCHEMA = 'payment.xsd';

    /**
     * Per file validation schema file name
     */
    const PER_FILE_VALIDATION_SCHEMA = 'payment_file.xsd';

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
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir('etc', 'Magento_Payment') . '/' . self::MERGED_CONFIG_SCHEMA;
        $this->_perFileSchema = $moduleReader->getModuleDir('etc', 'Magento_Payment')
            . '/' . self::PER_FILE_VALIDATION_SCHEMA;
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
        return $this->_perFileSchema;
    }
}
