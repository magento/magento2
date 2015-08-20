<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer\Config;

use Magento\Framework\Config\SchemaLocatorInterface;

class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * Get path to merged config schema
     *
     * @return string|null
     */
    public function getSchema()
    {
        return realpath(__DIR__ . '/../etc/') . '/indexer_merged.xsd';
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string|null
     */
    public function getPerFileSchema()
    {
        return realpath(__DIR__ . '/../etc/') . '/indexer.xsd';
    }
}
