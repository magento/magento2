<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Model\Plugin\Query\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\GraphQl\Model\Query\Resolver\ContextFactory as ResolverContextFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Plugin for injecting store information into resolver context
 */
class ContextFactory
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
     * @param ResolverContextFactory $subject
     * @param ContextInterface $resultContext
     * @return ContextInterface
     *
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreate(
        ResolverContextFactory $subject,
        ContextInterface $resultContext
    ) {
        $extensionAttributes = $resultContext->getExtensionAttributes();
        $extensionAttributes->setStoreId((int)$this->storeManager->getStore()->getId());
        $resultContext->setExtensionAttributes($extensionAttributes);

        return $resultContext;
    }
}
