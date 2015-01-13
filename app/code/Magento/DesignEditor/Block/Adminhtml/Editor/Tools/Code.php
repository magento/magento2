<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Tools;

/**
 * Block that renders Code tab (or Advanced tab)
 */
class Code extends \Magento\DesignEditor\Block\Adminhtml\Editor\Tools\Tabs\AbstractTabs
{
    /**
     * @var string Tab HTML identifier
     */
    protected $_htmlId = 'vde-tab-code';

    /**
     * @var string Tab HTML title
     */
    protected $_title = 'Advanced';

    /**
     * Get tabs data
     *
     * @return array
     */
    public function getTabs()
    {
        return [
            [
                'is_active' => true,
                'id' => 'vde-tab-css',
                'title' => strtoupper(__('CSS')),
                'content_block' => 'design_editor_tools_code_css',
            ],
            [
                'is_active' => false,
                'id' => 'vde-tab-js',
                'title' => strtoupper(__('JS')),
                'content_block' => 'design_editor_tools_code_js'
            ],
            [
                'is_active' => false,
                'id' => 'vde-tab-custom',
                'title' => strtoupper(__('Custom CSS')),
                'content_block' => 'design_editor_tools_code_custom'
            ],
            [
                'is_active' => false,
                'id' => 'vde-tab-image-sizing',
                'title' => strtoupper(__('Image Sizing')),
                'content_block' => 'design_editor_tools_code_image_sizing'
            ]
        ];
    }
}
