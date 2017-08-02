<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Model\Config\Source;

/**
 * Class Unitofmeasure
 * @since 2.0.0
 */
class Unitofmeasure extends \Magento\Ups\Model\Config\Source\Generic
{
    /**
     * Carrier code
     *
     * @var string
     * @since 2.0.0
     */
    protected $_code = 'unit_of_measure';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
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
