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
namespace Magento\DesignEditor\Block\Adminhtml\Editor;

/**
 * Block that renders VDE tools panel
 *
 * @method string getMode()
 * @method \Magento\DesignEditor\Block\Adminhtml\Editor\Tools setMode($mode)
 */
class Tools extends \Magento\Backend\Block\Template
{
    /**
     * Alias of tab handle block in layout
     */
    const TAB_HANDLE_BLOCK_ALIAS = 'tab_handle';

    /**
     * @var \Magento\DesignEditor\Model\Theme\Context
     */
    protected $_themeContext;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\DesignEditor\Model\Theme\Context $themeContext
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\DesignEditor\Model\Theme\Context $themeContext,
        array $data = array()
    ) {
        $this->_themeContext = $themeContext;
        parent::__construct($context, $data);
    }

    /**
     * Get tabs data
     *
     * @return array
     */
    public function getTabs()
    {
        return array(
            array(
                'is_hidden' => false,
                'is_disabled' => false,
                'id' => 'vde-tab-quick-styles',
                'label' => __('Quick Styles'),
                'content_block' => 'design_editor_tools_quick-styles',
                'class' => 'item-design'
            ),
            array(
                'is_hidden' => true,
                'is_disabled' => false,
                'id' => 'vde-tab-block',
                'label' => __('Block'),
                'content_block' => 'design_editor_tools_block',
                'class' => 'item-block'
            ),
            array(
                'is_hidden' => true,
                'is_disabled' => false,
                'id' => 'vde-tab-settings',
                'label' => __('Settings'),
                'content_block' => 'design_editor_tools_settings',
                'class' => 'item-settings'
            ),
            array(
                'is_hidden' => false,
                'is_disabled' => false,
                'id' => 'vde-tab-code',
                'label' => __('Advanced'),
                'content_block' => 'design_editor_tools_code',
                'class' => 'item-code'
            )
        );
    }

    /**
     * Get tabs html
     *
     * @return string[]
     */
    public function getTabContents()
    {
        $contents = array();
        foreach ($this->getTabs() as $tab) {
            $contents[] = $this->getChildHtml($tab['content_block']);
        }
        return $contents;
    }

    /**
     * Get tabs handles
     *
     * @return string[]
     */
    public function getTabHandles()
    {
        /** @var $tabHandleBlock \Magento\Backend\Block\Template */
        $tabHandleBlock = $this->getChildBlock(self::TAB_HANDLE_BLOCK_ALIAS);
        $handles = array();
        foreach ($this->getTabs() as $tab) {
            $href = '#' . $tab['id'];
            $handles[] = $tabHandleBlock->setIsHidden(
                $tab['is_hidden']
            )->setIsDisabled(
                $tab['is_disabled']
            )->setHref(
                $href
            )->setClass(
                $tab['class']
            )->setTitle(
                $tab['label']
            )->setLabel(
                $tab['label']
            )->toHtml();
        }

        return $handles;
    }

    /**
     * Get save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'adminhtml/system_design_editor_tools/saveQuickStyles',
            array('theme_id' => $this->_themeContext->getEditableTheme()->getId())
        );
    }
}
