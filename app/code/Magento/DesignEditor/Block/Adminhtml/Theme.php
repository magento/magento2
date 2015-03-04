<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml;

use Magento\Backend\Block\Widget\Button;

/**
 * Design editor theme
 *
 * @method \Magento\DesignEditor\Block\Adminhtml\Theme setTheme(\Magento\Framework\View\Design\ThemeInterface $theme)
 * @method \Magento\Framework\View\Design\ThemeInterface getTheme()
 */
class Theme extends \Magento\Backend\Block\Template
{
    /**
     * Buttons array
     *
     * @var Button[]
     */
    protected $_buttons = [];

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Add button
     *
     * @param Button $button
     * @return $this
     */
    public function addButton($button)
    {
        $this->_buttons[] = $button;
        return $this;
    }

    /**
     * Clear buttons
     *
     * @return $this
     */
    public function clearButtons()
    {
        $this->_buttons = [];
        return $this;
    }

    /**
     * Get buttons html
     *
     * @return string
     */
    public function getButtonsHtml()
    {
        $output = '';
        /** @var $button Button */
        foreach ($this->_buttons as $button) {
            $output .= $button->toHtml();
        }
        return $output;
    }

    /**
     * Return array of assigned stores titles
     *
     * @return string[]
     */
    public function getStoresTitles()
    {
        $storesTitles = [];
        /** @var $store \Magento\Store\Model\Store */
        foreach ($this->getTheme()->getAssignedStores() as $store) {
            $storesTitles[] = $store->getName();
        }
        return $storesTitles;
    }

    /**
     * Get options for JS widget vde.themeControl
     *
     * @return string
     */
    public function getOptionsJson()
    {
        $theme = $this->getTheme();
        $options = ['theme_id' => $theme->getId(), 'theme_title' => $theme->getThemeTitle()];

        /** @var $helper \Magento\Framework\Json\Helper\Data */
        $helper = $this->jsonHelper;
        return $helper->jsonEncode($options);
    }

    /**
     * Get quick save button
     *
     * @return Button
     */
    public function getQuickSaveButton()
    {
        /** @var $saveButton Button */
        $saveButton = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');
        $saveButton->setData(['label' => __('Save'), 'class' => 'action-save']);
        return $saveButton;
    }
}
