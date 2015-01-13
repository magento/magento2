<?php
/**
 * Locator for fieldset XSD schemas.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Object\Copy\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged config
     *
     * @var string
     */
    protected $_schema;

    /**
     * Path to corresponding XSD file with validation rules for separate config files
     *
     * @var string
     */
    protected $_perFileSchema;

    /**
     * @param Filesystem $filesystem
     * @param string $schema
     * @param string $perFileSchema
     */
    public function __construct(Filesystem $filesystem, $schema, $perFileSchema)
    {
        $rootDir = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->_schema = $rootDir->getAbsolutePath($schema);
        $this->_perFileSchema = $rootDir->getAbsolutePath($perFileSchema);
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
