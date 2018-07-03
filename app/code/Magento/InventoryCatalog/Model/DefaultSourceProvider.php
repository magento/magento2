<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;

/**
 * Service returns Default Source Id
 */
class DefaultSourceProvider implements DefaultSourceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return 'default';
    }
}
