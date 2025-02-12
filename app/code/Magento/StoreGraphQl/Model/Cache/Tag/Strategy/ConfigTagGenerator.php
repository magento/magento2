<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Cache\Tag\Strategy;

use Magento\Framework\App\Config\ValueInterface;
use Magento\Store\Model\Config\Cache\Tag\Strategy\TagGeneratorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity;

/**
 * Generator that generates cache tags for store configuration.
 */
class ConfigTagGenerator implements TagGeneratorInterface
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
    public function generateTags(ValueInterface $config): array
    {
        if ($config->getScope() == ScopeInterface::SCOPE_WEBSITES) {
            $website = $this->storeManager->getWebsite($config->getScopeId());
            $storeIds = $website->getStoreIds();
        } elseif ($config->getScope() == ScopeInterface::SCOPE_STORES) {
            $storeIds = [$config->getScopeId()];
        } else {
            $storeIds = array_keys($this->storeManager->getStores());
        }
        $tags = [];
        foreach ($storeIds as $storeId) {
            $tags[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, $storeId);
        }
        return $tags;
    }
}
