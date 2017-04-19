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
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for config (per-file)
     *
     * @var string
     */
    private $perFileSchema;

    /**
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $this->perFileSchema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Ui')
            . '/' . 'ui_configuration.xsd';
    }

    /**
     * @inheritdoc
     */
    public function getSchema()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}
