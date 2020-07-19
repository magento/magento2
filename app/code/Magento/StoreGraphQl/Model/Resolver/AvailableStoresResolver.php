<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\StoreGraphQl\Model\Resolver\Store\StoreConfigDataProvider;

/**
 * AvailableStores page field resolver, used for GraphQL request processing.
 */
class AvailableStoresResolver implements ResolverInterface
{
    /**
     * @var StoreConfigDataProvider
     */
    private $storeConfigDataProvider;

    /**
     * @param StoreConfigDataProvider $storeConfigsDataProvider
     */
    public function __construct(
        StoreConfigDataProvider $storeConfigsDataProvider
    ) {
        $this->storeConfigDataProvider = $storeConfigsDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return $this->storeConfigDataProvider->getAvailableStoreConfig(
            (int)$context->getExtensionAttributes()->getStore()->getWebsiteId()
        );
    }
}
