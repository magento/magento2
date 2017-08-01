<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

use Magento\Store\Model\ScopeInterface;

/**
 * Class \Magento\Store\Model\StoreResolver\ReaderList
 *
 * @since 2.0.0
 */
class ReaderList
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $resolverMap;

    // @codingStandardsIgnoreStart

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $resolverMap
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $resolverMap = [
            ScopeInterface::SCOPE_WEBSITE => \Magento\Store\Model\StoreResolver\Website::class,
            ScopeInterface::SCOPE_GROUP => \Magento\Store\Model\StoreResolver\Group::class,
            ScopeInterface::SCOPE_STORE => \Magento\Store\Model\StoreResolver\Store::class,
        ]
    ) {
        $this->resolverMap = $resolverMap;
        $this->objectManager = $objectManager;
    }

    // @codingStandardsIgnoreEnd

    /**
     * Retrieve store relation reader by run mode
     *
     * @param string $runMode
     * @return ReaderInterface
     * @since 2.0.0
     */
    public function getReader($runMode)
    {
        return $this->objectManager->get($this->resolverMap[$runMode]);
    }
}
