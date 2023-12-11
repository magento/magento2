<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Date;

/**
 * @api
 * @since 100.0.2
 */
class Short implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Method return array of date options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        $dateTime = new \DateTime();
        $arr[] = ['label' => '', 'value' => ''];
        $arr[] = ['label' => 'MM/DD/YY (' . $dateTime->format('m/d/y') . ')', 'value' => '%m/%d/%y'];
        $arr[] = ['label' => 'MM/DD/YYYY (' . $dateTime->format('m/d/Y') . ')', 'value' => '%m/%d/%Y'];
        $arr[] = ['label' => 'DD/MM/YY (' . $dateTime->format('d/m/y') . ')', 'value' => '%d/%m/%y'];
        $arr[] = ['label' => 'DD/MM/YYYY (' . $dateTime->format('d/m/Y') . ')', 'value' => '%d/%m/%Y'];
        return $arr;
    }
}
