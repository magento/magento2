<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DirectoryGraphQl\Model\Resolver\Country;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;
use Magento\Store\Model\StoreManagerInterface;

class Identity implements IdentityInterface
{
    /**
     * @var string
     */
    public const CACHE_TAG = 'gql_country';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getIdentities(array $resolvedData): array
    {
        if (empty($resolvedData)) {
            return [];
        }
        $storeId = $this->storeManager->getStore()->getId();
        return [self::CACHE_TAG, sprintf('%s_%s', self::CACHE_TAG, $storeId)];
    }
}
