<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

/**
 * Price model for external catalogs
 * @since 2.0.0
 */
class CatalogPriceFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
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
     * @since 2.0.0
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
