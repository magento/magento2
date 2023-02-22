<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Model\Cache\Tag\Strategy;

use Magento\DirectoryGraphQl\Model\Resolver\Country\Identity;
use Magento\Framework\App\Cache\Tag\StrategyInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Produce cache tags for country config.
 */
class CountryConfig implements StrategyInterface
{
    /**
     * @var string[]
     */
    private $countryConfigPaths = [
        'general/locale/code',
        'general/country/allow',
        'general/region/display_all',
        'general/region/state_required'
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
    public function getTags($object): array
    {
        if (!is_object($object)) {
            throw new \InvalidArgumentException('Provided argument is not an object');
        }

        if ($object instanceof ValueInterface
            && in_array($object->getPath(), $this->countryConfigPaths)
            && $object->isValueChanged()
        ) {
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
                $ids[] = sprintf('%s_%s', Identity::CACHE_TAG, $storeId);
            }
            return $ids;
        }

        return [];
    }
}
