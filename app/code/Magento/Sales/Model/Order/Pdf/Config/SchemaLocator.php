<?php
/**
 * Attributes config schema locator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Pdf\Config;

use Magento\Framework\Module\Dir;

/**
 * Class \Magento\Sales\Model\Order\Pdf\Config\SchemaLocator
 *
 * @since 2.0.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged configs
     *
     * @var string
     * @since 2.0.0
     */
    private $_schema;

    /**
     * Path to corresponding XSD file with validation rules for individual configs
     *
     * @var string
     * @since 2.0.0
     */
    private $_schemaFile;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $dir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Sales');
        $this->_schema = $dir . '/pdf.xsd';
        $this->_schemaFile = $dir . '/pdf_file.xsd';
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
        return $this->_schemaFile;
    }
}
