<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Dependency;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\SchemaLocatorInterface;

class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * @var string
     */
    private $schema;

    /**
     * @var string
     */
    private $perFileSchema;

    public function __construct(ComponentRegistrar $componentRegistrar)
    {
        $module_path = $componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Magento_Webapi');
        $this->schema = $module_path . '/etc/webapi_merged.xsd';
        $this->perFileSchema = $module_path . '/etc/webapi.xsd';
    }

    /**
     * Return webapi_merged.xsd path
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Return webapi.xsd path
     *
     * @return string
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}
