<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\StoreResolver;

use Magento\Store\Model\ScopeInterface;

class ReaderList
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $resolverMap;

    // @codingStandardsIgnoreStart
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $resolverMap
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $resolverMap = [
            ScopeInterface::SCOPE_WEBSITE => '\Magento\Store\Model\StoreResolver\Website',
            ScopeInterface::SCOPE_GROUP => '\Magento\Store\Model\StoreResolver\Group',
            ScopeInterface::SCOPE_STORE => '\Magento\Store\Model\StoreResolver\Store',
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
     */
    public function getReader($runMode)
    {
        return $this->objectManager->get($this->resolverMap[$runMode]);
    }
}
