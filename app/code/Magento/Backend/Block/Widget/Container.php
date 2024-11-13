<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Widget;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Item as ButtonItem;

/**
 * Backend container block
 *
 * @api
 * @since 100.0.2
 */
class Container extends Template implements ContainerInterface
{
    /**
     * Initialization parameters in pseudo-constructor
     */
    public const PARAM_CONTROLLER = 'controller';

    public const PARAM_HEADER_TEXT = 'header_text';

    /**
     * @var string
     */
    protected $_controller = 'empty';

    /**
     * @var string
     */
    protected $_headerText = 'Container Widget Header';

    /**
     * @var ButtonList
     */
    protected $buttonList;

    /**
     * @var Button\ToolbarInterface
     */
    protected $toolbar;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, array $data = [])
    {
        $this->buttonList = $context->getButtonList();
        $this->toolbar = $context->getButtonToolbar();
        parent::__construct($context, $data);
    }

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
     * Public wrapper for the button list
     *
     * @param string $buttonId
     * @param array $data
     * @param integer $level
     * @param integer $sortOrder
     * @param string|null $region That button should be displayed in ('toolbar', 'header', 'footer', null)
     * @return $this
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        $this->buttonList->add($buttonId, $data, $level, $sortOrder, $region);
        return $this;
    }

    /**
     * Public wrapper for the button list
     *
     * @param string $buttonId
     * @return $this
     */
    public function removeButton($buttonId)
    {
        $this->buttonList->remove($buttonId);
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
        $this->buttonList->update($buttonId, $key, $data);
        return $this;
    }

    /**
     * Preparing child blocks for each added button
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->toolbar->pushButtons($this, $this->buttonList);
        return parent::_prepareLayout();
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
        foreach ($this->buttonList->getItems() as $buttons) {
            /** @var ButtonItem $item */
            foreach ($buttons as $item) {
                if ($region && $region != $item->getRegion()) {
                    continue;
                }
                $out .= $this->getChildHtml($item->getButtonKey());
            }
        }
        return $out;
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
        return 'head-' . strtr($this->_controller ?? '', '_', '-');
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
        foreach ($this->buttonList->getItems() as $buttons) {
            foreach ($buttons as $data) {
                if (isset($data['region']) && 'footer' == $data['region']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check whether button rendering is allowed in current context
     *
     * @param ButtonItem $item
     * @return bool
     */
    public function canRender(Button\Item $item)
    {
        return !$item->isDeleted();
    }
}
