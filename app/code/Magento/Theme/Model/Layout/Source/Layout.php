<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            $this->_options = array();
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
        $options = array();

        foreach ($this->getOptions() as $value => $label) {
            $options[] = array('label' => $label, 'value' => $value);
        }

        if ($withEmpty) {
            array_unshift($options, array('value' => '', 'label' => __('-- Please Select --')));
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
