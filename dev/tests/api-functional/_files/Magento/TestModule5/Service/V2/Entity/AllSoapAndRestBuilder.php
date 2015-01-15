<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModule5\Service\V2\Entity;

use Magento\TestModule5\Service\V1\Entity;

class AllSoapAndRestBuilder extends \Magento\TestModule5\Service\V1\Entity\AllSoapAndRestBuilder
{
    const PRICE = 'price';

    /**
     * @param int $price
     * @return \Magento\TestModule5\Service\V2\Entity\AllSoapAndRestBuilder
     */
    public function setPrice($price)
    {
        return $this->_set(self::PRICE, $price);
    }
}
