<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
