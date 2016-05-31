<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule5\Service\V2\Entity;

/**
 * Some Data Object short description.
 *
 * Data Object long
 * multi line description.
 */
class AllSoapAndRest extends \Magento\TestModule5\Service\V2\AllSoapAndRest
{
    /**
     * Price field
     */
    const PRICE = 'price';

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->_get(self::PRICE);
    }
}
