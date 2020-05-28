<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Config;

use Magento\Framework\Config\SchemaLocatorInterface;

/**
 * Schema locator for tests config
 */
class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * @inheritdoc
     */
    public function getSchema()
    {
        return __DIR__ . '/../../etc/overrides.xsd';
    }

    /**
     * @inheritdoc
     */
    public function getPerFileSchema()
    {
        return null;
    }
}
