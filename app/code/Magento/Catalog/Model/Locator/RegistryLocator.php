<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Locator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;

/**
 * Class RegistryLocator
 */
class RegistryLocator implements LocatorInterface
{
    /**
     * @var Registry
     */
    protected $registry;

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
        if ($product = $this->registry->registry('current_product')) {
            return $product;
        }

        throw new NotFoundException(__('Product was not registered'));
    }

    /**
     * {@inheritdoc}
     * @throws NotFoundException
     */
    public function getStore()
    {
        if ($currentStore = $this->registry->registry('current_store')) {
            return $currentStore;
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
