<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
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
