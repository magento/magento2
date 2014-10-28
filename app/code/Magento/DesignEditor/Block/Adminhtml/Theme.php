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
    protected $_buttons = array();

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Helper\Data $coreHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Helper\Data $coreHelper,
        array $data = array()
    ) {
        $this->_coreHelper = $coreHelper;
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
        $this->_buttons = array();
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
        $storesTitles = array();
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
        $options = array('theme_id' => $theme->getId(), 'theme_title' => $theme->getThemeTitle());

        /** @var $helper \Magento\Core\Helper\Data */
        $helper = $this->_coreHelper;
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
        $saveButton->setData(array('label' => __('Save'), 'class' => 'action-save'));
        return $saveButton;
    }
}
