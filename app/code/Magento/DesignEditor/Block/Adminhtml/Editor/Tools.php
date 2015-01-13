<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        array $data = []
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
        return [
            [
                'is_hidden' => false,
                'is_disabled' => false,
                'id' => 'vde-tab-quick-styles',
                'label' => __('Quick Styles'),
                'content_block' => 'design_editor_tools_quick-styles',
                'class' => 'item-design',
            ],
            [
                'is_hidden' => true,
                'is_disabled' => false,
                'id' => 'vde-tab-block',
                'label' => __('Block'),
                'content_block' => 'design_editor_tools_block',
                'class' => 'item-block'
            ],
            [
                'is_hidden' => true,
                'is_disabled' => false,
                'id' => 'vde-tab-settings',
                'label' => __('Settings'),
                'content_block' => 'design_editor_tools_settings',
                'class' => 'item-settings'
            ],
            [
                'is_hidden' => false,
                'is_disabled' => false,
                'id' => 'vde-tab-code',
                'label' => __('Advanced'),
                'content_block' => 'design_editor_tools_code',
                'class' => 'item-code'
            ]
        ];
    }

    /**
     * Get tabs html
     *
     * @return string[]
     */
    public function getTabContents()
    {
        $contents = [];
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
        $handles = [];
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
            ['theme_id' => $this->_themeContext->getEditableTheme()->getId()]
        );
    }
}
