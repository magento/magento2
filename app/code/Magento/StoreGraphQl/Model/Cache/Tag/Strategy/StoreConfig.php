<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Cache\Tag\Strategy;

use Magento\Framework\App\Cache\Tag\StrategyInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity;

/**
 * Produce cache tags for store config.
 */
class StoreConfig implements StrategyInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }
    /**
     * @inheritdoc
     */
    public function getTags($object): array
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }

        if ($object instanceof ValueInterface && $object->isValueChanged()) {
            if ($object->getScope() == ScopeInterface::SCOPE_WEBSITES) {
                $website = $this->storeManager->getWebsite($object->getScopeId());
                $storeIds = $website->getStoreIds();
            } elseif ($object->getScope() == ScopeInterface::SCOPE_STORES) {
                $storeIds = [$object->getScopeId()];
            } else {
                $storeIds = array_keys($this->storeManager->getStores());
            }
            $ids = [];
            foreach ($storeIds as $storeId) {
                $ids[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, $storeId);
            }
            return $ids;
        }

        return [];
    }
}
