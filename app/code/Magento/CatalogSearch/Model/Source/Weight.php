<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Source;

/**
 * Attribute weight options
 */
class Weight implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Quick search weights
     *
     * @var int[]
     */
    protected $weights = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

    /**
     * Retrieve search weights as options array
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getValues() as $value) {
            $res[] = ['value' => $value, 'label' => $value];
        }
        return $res;
    }

    /**
     * Retrieve search weights array
     *
     * @return int[]
     */
    public function getValues()
    {
        return $this->weights;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
