<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Locator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class RegistryLocator
 */
class RegistryLocator implements LocatorInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var ProductInterface
     */
    private $product;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getProduct()
    {
        if (null !== $this->product) {
            return $this->product;
        }

        if ($product = $this->registry->registry('current_product')) {
            return $this->product = $product;
        }

        throw new NotFoundException(__('Product was not registered'));
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getStore()
    {
        if (null !== $this->store) {
            return $this->store;
        }

        if ($store = $this->registry->registry('current_store')) {
            return $this->store = $store;
        }

        throw new NotFoundException(__('Store was not registered'));
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        return $this->getProduct()->getWebsiteIds();
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseCurrencyCode()
    {
        return $this->getStore()->getBaseCurrencyCode();
    }
}
