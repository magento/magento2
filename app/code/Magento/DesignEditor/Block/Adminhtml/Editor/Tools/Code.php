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
        return array(
            array(
                'is_active' => true,
                'id' => 'vde-tab-css',
                'title' => strtoupper(__('CSS')),
                'content_block' => 'design_editor_tools_code_css'
            ),
            array(
                'is_active' => false,
                'id' => 'vde-tab-js',
                'title' => strtoupper(__('JS')),
                'content_block' => 'design_editor_tools_code_js'
            ),
            array(
                'is_active' => false,
                'id' => 'vde-tab-custom',
                'title' => strtoupper(__('Custom CSS')),
                'content_block' => 'design_editor_tools_code_custom'
            ),
            array(
                'is_active' => false,
                'id' => 'vde-tab-image-sizing',
                'title' => strtoupper(__('Image Sizing')),
                'content_block' => 'design_editor_tools_code_image_sizing'
            )
        );
    }
}
