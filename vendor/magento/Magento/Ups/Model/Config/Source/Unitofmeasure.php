<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ups\Model\Config\Source;

/**
 * Class Unitofmeasure
 */
class Unitofmeasure extends \Magento\Ups\Model\Config\Source\Generic
{
    /**
     * Carrier code
     *
     * @var string
     */
    protected $_code = 'unit_of_measure';

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $unitArr = $this->carrierConfig->getCode($this->_code);
        $returnArr = [];
        foreach ($unitArr as $key => $val) {
            $returnArr[] = ['value' => $key, 'label' => $key];
        }
        return $returnArr;
    }
}
