<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source;

/**
 * Catalog products per page on List mode source
 */
class ListPerPage implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $_pagerOptions;

    /**
     * Constructor
     *
     * @param string $options
     */
    public function __construct($options)
    {
        $this->_pagerOptions = $options !== null ? explode(',', $options) : [];
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $output = [];
        foreach ($this->_pagerOptions as $option) {
            $output[] = ['value' => $option, 'label' => $option];
        }
        return $output;
    }
}
