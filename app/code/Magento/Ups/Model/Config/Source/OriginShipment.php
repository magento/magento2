<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
