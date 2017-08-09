<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Reader\Definition;

use Magento\Framework\Module\Dir;

/**
 * Config schema locator interface
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    private $schema;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $this->schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Ui') . '/' . 'ui_definition.xsd';
    }

    /**
     * @inheritdoc
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @inheritdoc
     */
    public function getPerFileSchema()
    {
        return null;
    }
}
