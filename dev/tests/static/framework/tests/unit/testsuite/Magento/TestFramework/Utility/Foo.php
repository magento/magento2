<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility;

use Magento\Framework\UrlFactory;
use Magento\Catalog\Model\Product;

class Foo
{
    /**
     * Constructor
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterfaceFactory|null $customerRepositoryFactory
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterfaceFactory $customerRepositoryFactory = null
    ) {
    }

    /**
     * @return \Magento\Catalog\Model\Indexer\Product\Flat\Table\BuilderInterfaceFactory
     */
    public function getBuilderFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Indexer\Product\Flat\Table\BuilderInterfaceFactory::class
        );
    }

    /**
     * @return BarFactory
     */
    public function getBarFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(BarFactory::class);
    }

    /**
     * @return PartialNamespace\BarFactory
     */
    public function getPartialNamespaceBarFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(PartialNamespace\BarFactory::class);
    }

    /**
     * @return UrlFactory
     */
    public function getUrlFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(UrlFactory::class);
    }

    /**
     * @return Product\OptionFactory
     */
    public function getOptionFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(Product\OptionFactory::class);
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory
     */
    public function getProductLinkFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
            ->get(
                \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory::class
            );
    }

    /**
     * @return \Magento\Customer\Api\CustomerRepositoryInterfaceFactory
     */
    public function getCustomerRepositoryFactory()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Customer\Api\CustomerRepositoryInterfaceFactory::class
        );
    }
}
