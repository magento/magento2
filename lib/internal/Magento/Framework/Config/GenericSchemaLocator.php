<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config;

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;

/**
 * Class GenericSchemaLocator
 */
class GenericSchemaLocator implements SchemaLocatorInterface
{
    /**
     * @var ModuleDirReader
     */
    private $moduleDirReader;
    
    /**
     * @var string
     */
    private $moduleName;
    
    /**
     * @var string
     */
    private $perFileSchema;
    
    /**
     * @var string|null
     */
    private $schema;

    /**
     * @param ModuleDirReader $reader
     * @param string $moduleName
     * @param string $schema
     * @param string|null $perFileSchema
     */
    public function __construct(ModuleDirReader $reader, $moduleName, $schema, $perFileSchema = null)
    {
        $this->moduleDirReader = $reader;
        $this->moduleName = $moduleName;
        $this->schema = $schema;
        $this->perFileSchema = $perFileSchema;
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->moduleDirReader->getModuleDir(Dir::MODULE_ETC_DIR, $this->moduleName) . '/' . $this->schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        if ($this->perFileSchema !== null) {
            return $this->moduleDirReader->getModuleDir(Dir::MODULE_ETC_DIR, $this->moduleName)
            . '/' . $this->perFileSchema;
        }
    }
}
