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
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Block\Widget;

/**
 * Backend container block
 *
 * @category    Magento
 * @package     Magento_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Container extends \Magento\Backend\Block\Template
{
    /**#@+
     * Initialization parameters in pseudo-constructor
     */
    const PARAM_CONTROLLER  = 'controller';
    const PARAM_HEADER_TEXT = 'header_text';
    /**#@-*/

    /**
     * So called "container controller" to specify group of blocks participating in some action
     *
     * @var string
     */
    protected $_controller = 'empty';

    /**
     * Array of buttons
     *
     * @var array
     */
    protected $_buttons = array(
        -1  => array(),
        0   => array(),
        1   => array(),
    );

    /**
     * Header text
     *
     * @var string
     */
    protected $_headerText = 'Container Widget Header';

    /**
     * Initialize "controller" and "header text"
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->hasData(self::PARAM_CONTROLLER)) {
            $this->_controller = $this->_getData(self::PARAM_CONTROLLER);
        }
        if ($this->hasData(self::PARAM_HEADER_TEXT)) {
            $this->_headerText = $this->_getData(self::PARAM_HEADER_TEXT);
        }
    }

    /**
     * Add a button
     *
     * @param string $buttonId
     * @param array $data
     * @param integer $level
     * @param integer $sortOrder
     * @param string|null $region That button should be displayed in ('header', 'footer', null)
     * @return $this
     */
    protected function _addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'header')
    {
        if (!isset($this->_buttons[$level])) {
            $this->_buttons[$level] = array();
        }
        if (empty($data['id'])) {
            $data['id'] = $buttonId;
        }
        $this->_buttons[$level][$buttonId] = $data;
        $this->_buttons[$level][$buttonId]['region'] = $region;
        if (empty($this->_buttons[$level][$buttonId]['id'])) {
            $this->_buttons[$level][$buttonId]['id'] = $buttonId;
        }
        if ($sortOrder) {
            $this->_buttons[$level][$buttonId]['sort_order'] = $sortOrder;
        } else {
            $this->_buttons[$level][$buttonId]['sort_order'] = count($this->_buttons[$level]) * 10;
        }
        return $this;
    }

    /**
     * Public wrapper for protected _addButton method
     *
     * @param string $buttonId
     * @param array $data
     * @param integer $level
     * @param integer $sortOrder
     * @param string|null $region That button should be displayed in ('header', 'footer', null)
     * @return $this
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'header')
    {
        return $this->_addButton($buttonId, $data, $level, $sortOrder, $region);
    }

    /**
     * Remove existing button
     *
     * @param string $buttonId
     * @return $this
     */
    protected function _removeButton($buttonId)
    {
        foreach ($this->_buttons as $level => $buttons) {
            if (isset($buttons[$buttonId])) {
                unset($this->_buttons[$level][$buttonId]);
            }
        }
        return $this;
    }

    /**
     * Public wrapper for the _removeButton() method
     *
     * @param string $buttonId
     * @return $this
     */
    public function removeButton($buttonId)
    {
        return $this->_removeButton($buttonId);
    }

    /**
     * Update specified button property
     *
     * @param string $buttonId
     * @param string|null $key
     * @param string $data
     * @return $this
     */
    protected function _updateButton($buttonId, $key, $data)
    {
        foreach ($this->_buttons as $level => $buttons) {
            if (isset($buttons[$buttonId])) {
                if (!empty($key)) {
                    if ($child = $this->getChildBlock($buttonId . '_button')) {
                        $child->setData($key, $data);
                    }
                    if ('level' == $key) {
                        $this->_buttons[$data][$buttonId] = $this->_buttons[$level][$buttonId];
                        unset($this->_buttons[$level][$buttonId]);
                    } else {
                        $this->_buttons[$level][$buttonId][$key] = $data;
                    }
                } else {
                    $this->_buttons[$level][$buttonId] = $data;
                }
                break;
            }
        }
        return $this;
    }

    /**
     * Public wrapper for protected _updateButton method
     *
     * @param string $buttonId
     * @param string|null $key
     * @param string $data
     * @return $this
     */
    public function updateButton($buttonId, $key, $data)
    {
        return $this->_updateButton($buttonId, $key, $data);
    }

    /**
     * Preparing child blocks for each added button
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        foreach ($this->_buttons as $buttons) {
            foreach ($buttons as $buttonId => $data) {
                $childId = $this->_prepareButtonBlockId($buttonId);
                $blockClassName = isset($data['class_name']) ? $data['class_name'] : null;
                $this->_addButtonChildBlock($childId, $blockClassName);
            }
        }
        return parent::_prepareLayout();
    }

    /**
     * Prepare block id for button's id
     *
     * @param string $buttonId
     * @return string
     */
    protected function _prepareButtonBlockId($buttonId)
    {
        return $buttonId . '_button';
    }

    /**
     * Adding child block with specified child's id.
     *
     * @param string $childId
     * @param null|string $blockClassName
     * @return \Magento\Backend\Block\Widget
     */
    protected function _addButtonChildBlock($childId, $blockClassName = null)
    {
        if (null === $blockClassName) {
            $blockClassName = 'Magento\Backend\Block\Widget\Button';
        }
        $block = $this->getLayout()->createBlock($blockClassName, $this->getNameInLayout() . '-' . $childId);
        $this->setChild($childId, $block);
        return $block;
    }

    /**
     * Produce buttons HTML
     *
     * @param string $region
     * @return string
     */
    public function getButtonsHtml($region = null)
    {
        $out = '';
        foreach ($this->_buttons as $buttons) {
            $_buttons = $this->_sortButtons($buttons);
            foreach ($_buttons as $button) {
                $buttonId = $button['id'];
                $data = $button['data'];
                if ($region && isset($data['region']) && ($region != $data['region'])) {
                    continue;
                }
                $childId = $this->_prepareButtonBlockId($buttonId);
                $child = $this->getChildBlock($childId);

                if (!$child) {
                    $blockClassName = isset($data['class_name']) ? $data['class_name'] : null;
                    $child = $this->_addButtonChildBlock($childId, $blockClassName);
                }
                if (isset($data['name'])) {
                    $data['element_name'] = $data['name'];
                }
                $child->setData($data);

                $out .= $this->getChildHtml($childId);
            }
        }
        return $out;
    }

    /**
     * Sort buttons by sort order
     *
     * @param array $buttons
     * @return array
     */
    public function _sortButtons($buttons)
    {
        $_buttons = array();
        foreach ($buttons as $buttonId => $data) {
            $_buttons[$data['sort_order']]['id'] = $buttonId;
            $_buttons[$data['sort_order']]['data'] = $data;
        }
        ksort($_buttons);
        return $_buttons;
    }

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return $this->_headerText;
    }

    /**
     * Get header CSS class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-' . strtr($this->_controller, '_', '-');
    }

    /**
     * Get header HTML
     *
     * @return string
     */
    public function getHeaderHtml()
    {
        return '<h3 class="' . $this->getHeaderCssClass() . '">' . $this->getHeaderText() . '</h3>';
    }

    /**
     * Check if there's anything to display in footer
     *
     * @return boolean
     */
    public function hasFooterButtons()
    {
        foreach ($this->_buttons as $buttons) {
            foreach ($buttons as $data) {
                if (isset($data['region']) && ('footer' == $data['region'])) {
                    return true;
                }
            }
        }
        return false;
    }
}
