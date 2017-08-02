<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\CustomerData;

use Magento\Framework\Module\Dir;

/**
 * Class \Magento\Customer\CustomerData\SchemaLocator
 *
 * @since 2.0.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     * @since 2.0.0
     */
    protected $_schema;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Customer') . '/' . 'sections.xsd';
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPerFileSchema()
    {
        return null;
    }
}
