<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Model\Config\Source;

/**
 * @api
 * @since 2.0.0
 */
class Tablerate implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Tablerate
     * @since 2.0.0
     */
    protected $_carrierTablerate;

    /**
     * @param \Magento\OfflineShipping\Model\Carrier\Tablerate $carrierTablerate
     * @since 2.0.0
     */
    public function __construct(\Magento\OfflineShipping\Model\Carrier\Tablerate $carrierTablerate)
    {
        $this->_carrierTablerate = $carrierTablerate;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $arr = [];
        foreach ($this->_carrierTablerate->getCode('condition_name') as $k => $v) {
            $arr[] = ['value' => $k, 'label' => $v];
        }
        return $arr;
    }
}
