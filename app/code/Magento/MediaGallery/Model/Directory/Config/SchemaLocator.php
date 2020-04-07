<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaGallery\Model\Directory\Config;

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Config\SchemaLocatorInterface;

class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for both individual and merged configs
     *
     * @var string
     */
    private $schema;

    /**
     * @param Reader $moduleReader
     */
    public function __construct(Reader $moduleReader)
    {
        $this->schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'Magento_MediaGallery') . '/directory.xsd';
    }

    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Get path to per file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return $this->schema;
    }
}
