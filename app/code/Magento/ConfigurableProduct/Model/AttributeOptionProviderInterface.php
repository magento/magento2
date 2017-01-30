<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

/**
 * Interface to retrieve options for attribute
 */
interface AttributeOptionProviderInterface
{
    /**
     * Retrieve options for attribute
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param int $productId
     * @return array
     */
    public function getAttributeOptions(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute, $productId);

    /**
     * Retrieve in stock options for attribute
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param int $productId
     * @return array
     */
    public function getInStockAttributeOptions(
        \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute,
        $productId
    );
}
