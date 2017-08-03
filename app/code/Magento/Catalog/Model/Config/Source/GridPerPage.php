<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Source;

/**
 * Catalog products per page on Grid mode source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class GridPerPage implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options
     *
     * @var array
     * @since 2.0.0
     */
    protected $_options;

    /**
     * Constructor
     *
     * @param string $perPageValues
     * @since 2.0.0
     */
    public function __construct($perPageValues)
    {
        $this->_options = explode(',', $perPageValues);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
