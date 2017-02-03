<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Source;

/**
 * Attribute weight options
 */
class Weight
{
    /**
     * Quick search weights
     *
     * @var int[]
     */
    protected $_weights = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

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
        return $this->_weights;
    }
}
