<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index\Config;

use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Module\Dir;

/**
 * Class \Magento\Elasticsearch\Model\Adapter\Index\Config\SchemaLocator
 *
 * @since 2.1.0
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * XML schema for config file.
     */
    const CONFIG_FILE_SCHEMA = 'esconfig.xsd';

    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     * @since 2.1.0
     */
    protected $schema = null;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     * @var string
     * @since 2.1.0
     */
    protected $perFileSchema = null;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @since 2.1.0
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader)
    {
        $configDir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_Elasticsearch');
        $this->schema = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE_SCHEMA;
        $this->perFileSchema = $configDir . DIRECTORY_SEPARATOR . self::CONFIG_FILE_SCHEMA;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}
