<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config\Reader;

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

/**
 * Config schema locator interface
 * @since 2.2.0
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for config (per-file)
     *
     * @var string
     * @since 2.2.0
     */
    private $perFileSchema;

    /**
     * @param Reader $moduleReader
     * @since 2.2.0
     */
    public function __construct(Reader $moduleReader)
    {
        $this->perFileSchema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Ui')
            . '/' . 'ui_configuration.xsd';
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getSchema()
    {
        return null;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}
