<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Model\Config\Source;

/**
 * Class OriginShipment
 * @since 2.0.0
 */
class OriginShipment extends \Magento\Ups\Model\Config\Source\Generic
{
    /**
     * Carrier code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = 'originShipment';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
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
