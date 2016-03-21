<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Model\Config\Source;

/**
 * Class OriginShipment
 */
class OriginShipment extends \Magento\Ups\Model\Config\Source\Generic
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = 'originShipment';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function toOptionArray()
    {
        $orShipArr = $this->carrierConfig->getCode($this->_code);
        $returnArr = [];
        foreach ($orShipArr as $key => $val) {
            $returnArr[] = ['value' => $key, 'label' => $key];
        }
        return $returnArr;
    }
}
