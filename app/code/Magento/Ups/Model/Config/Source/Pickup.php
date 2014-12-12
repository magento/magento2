<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ups\Model\Config\Source;

/**
 * Class Pickup
 */
class Pickup extends \Magento\Ups\Model\Config\Source\Generic
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = 'pickup';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $ups = $this->carrierConfig->getCode($this->_code);
        $arr = [];
        foreach ($ups as $k => $v) {
            $arr[] = ['value' => $k, 'label' => __($v['label'])];
        }
        return $arr;
    }
}
