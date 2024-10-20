<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source;

/**
 * Catalog products per page on Grid mode source
 */
class GridPerPage implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_options;

    /**
     * Constructor
     *
     * @param string $perPageValues
     */
    public function __construct($perPageValues)
    {
        $this->_options = $perPageValues !== null ? explode(',', $perPageValues) : [];
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->_options as $option) {
            $result[] = ['value' => $option, 'label' => $option];
        }
        return $result;
    }
}
