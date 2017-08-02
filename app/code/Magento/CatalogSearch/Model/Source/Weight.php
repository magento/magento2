<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Source;

/**
 * Attribute weight options
 * @api
 * @since 2.0.0
 */
class Weight implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Quick search weights
     *
     * @var int[]
     * @since 2.1.0
     */
    protected $weights = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

    /**
     * Retrieve search weights as options array
     *
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getValues()
    {
        return $this->weights;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     * @since 2.1.0
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
