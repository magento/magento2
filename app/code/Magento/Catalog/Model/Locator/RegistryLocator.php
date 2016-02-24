<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Locator;

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
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * {@inheritdoc}
     */
    public function getStore()
    {
        return $this->registry->registry('current_store');
    }


    /**
     * {@inheritdoc}
     */
    public function getWebsiteIds()
    {
        return $this->registry->registry('current_product')->getWebsiteIds();
    }


    /**
     * {@inheritdoc}
     */
    public function getBaseCurrencyCode()
    {
        return $this->getStore()->getBaseCurrencyCode();
    }
}
