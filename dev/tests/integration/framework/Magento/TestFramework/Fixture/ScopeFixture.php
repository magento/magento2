<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Creates an image fixture
 *
 * Example: Basic usage. This will get the global scope
 *  ```php
 *     #[
 *         DataFixture(ScopeFixture::class, as: 'global_scope')
 *     ],
 *  ```
 * Example: Get store scope
 *
 * ```php
 *    #[
 *        DataFixture(ScopeFixture::class, ['code' => 'default'], as: 'default_store')
 *    ],
 * ```
 * Example: Get website scope
 *
 * ```php
 *    #[
 *        DataFixture(ScopeFixture::class, ['type' => 'website', code' => 'base'], as: 'default_website')
 *    ],
 * ```
 */
class ScopeFixture implements DataFixtureInterface
{
    private const DEFAULT_DATA = [
        'type' => ScopeInterface::SCOPE_STORE,
        'code' => 'admin',
    ];

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        return match ($data['type']) {
            ScopeInterface::SCOPE_STORE, ScopeInterface::SCOPE_STORES
            => $this->storeManager->getStore($data['code']),
            ScopeInterface::SCOPE_GROUP, ScopeInterface::SCOPE_GROUPS
            => $this->storeManager->getGroup($data['code']),
            ScopeInterface::SCOPE_WEBSITE, ScopeInterface::SCOPE_WEBSITES
            => $this->storeManager->getWebsite($data['code']),
            default => throw new \InvalidArgumentException('Invalid scope'),
        };
    }
}
