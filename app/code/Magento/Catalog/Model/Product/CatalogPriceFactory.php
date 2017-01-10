<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

/**
 * Price model for external catalogs
 */
class CatalogPriceFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Provide custom price model with basic validation
     *
     * @param string $name
     * @return \Magento\Catalog\Model\Product\CatalogPriceInterface
     * @throws \UnexpectedValueException
     */
    public function create($name)
    {
        $customPriceModel = $this->objectManager->get($name);
        if (!$customPriceModel instanceof \Magento\Catalog\Model\Product\CatalogPriceInterface) {
            throw new \UnexpectedValueException(
                'Class ' . $name . ' should be an instance of \Magento\Catalog\Model\Product\CatalogPriceInterface'
            );
        }

        return $customPriceModel;
    }
}
