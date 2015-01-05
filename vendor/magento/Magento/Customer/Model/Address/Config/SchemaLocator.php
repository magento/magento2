<?php
/**
 * Customer address format configuration schema locator
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Model\Address\Config;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    private $_schema;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $this->_schema = $moduleReader->getModuleDir('etc', 'Magento_Customer') . '/address_formats.xsd';
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return $this->_schema;
    }
}
