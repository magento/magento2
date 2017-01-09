<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Layout\Source;

class Layout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Theme\Model\Layout\Config
     */
    protected $_config;

    /**
     * @param \Magento\Theme\Model\Layout\Config $config
     */
    public function __construct(\Magento\Theme\Model\Layout\Config $config)
    {
        $this->_config = $config;
    }

    /**
     * Page layout options
     *
     * @var array
     */
    protected $_options = null;

    /**
     * Default option
     * @var string
     */
    protected $_defaultValue = null;

    /**
     * Retrieve page layout options
     *
     * @return array
     */
    public function getOptions()
    {
        if ($this->_options === null) {
            $this->_options = [];
            foreach ($this->_config->getPageLayouts() as $layout) {
                $this->_options[$layout->getCode()] = $layout->getLabel();
                if ($layout->getIsDefault()) {
                    $this->_defaultValue = $layout->getCode();
                }
            }
        }

        return $this->_options;
    }

    /**
     * Retrieve page layout options array
     *
     * @param bool $withEmpty
     * @return array
     */
    public function toOptionArray($withEmpty = false)
    {
        $options = [];

        foreach ($this->getOptions() as $value => $label) {
            $options[] = ['label' => $label, 'value' => $value];
        }

        if ($withEmpty) {
            array_unshift($options, ['value' => '', 'label' => __('-- Please Select --')]);
        }

        return $options;
    }

    /**
     * Default options value getter
     * @return string
     */
    public function getDefaultValue()
    {
        $this->getOptions();
        return $this->_defaultValue;
    }
}
