<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source;

/**
 * Catalog products per page on List mode source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class ListPerPage implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Pager Options
     *
     * @var array
     * @since 2.0.0
     */
    protected $_pagerOptions;

    /**
     * Constructor
     *
     * @param string $options
     * @since 2.0.0
     */
    public function __construct($options)
    {
        $this->_pagerOptions = explode(',', $options);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
