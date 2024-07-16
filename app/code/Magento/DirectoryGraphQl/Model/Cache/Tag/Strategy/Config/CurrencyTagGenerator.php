<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Model\Cache\Tag\Strategy\Config;

use Magento\DirectoryGraphQl\Model\Resolver\Currency\Identity;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Config\Cache\Tag\Strategy\TagGeneratorInterface;

/**
 * Generator that generates cache tags for currency configuration
 */
class CurrencyTagGenerator implements TagGeneratorInterface
{
    /**
     * @var string[]
     */
    private $currencyConfigPaths = [
        'currency/options/base',
        'currency/options/default',
        'currency/options/allow',
        'currency/options/customsymbol'
    ];

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
        if (in_array($config->getPath(), $this->currencyConfigPaths)) {
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
                $tags[] = sprintf('%s_%s', Identity::CACHE_TAG, $storeId);
            }
            return $tags;
        }
        return [];
    }
}
