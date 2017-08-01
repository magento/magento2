<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Block\Adminhtml\Template\Grid\Renderer;

/**
 * Adminhtml system templates grid block type item renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Type extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Email template types
     *
     * @var array
     * @since 2.0.0
     */
    protected static $_types = [
        \Magento\Framework\App\TemplateTypesInterface::TYPE_HTML => 'HTML',
        \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT => 'Text',
    ];

    /**
     * Render grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $str = __('Unknown');

        if (isset(self::$_types[$row->getTemplateType()])) {
            $str = self::$_types[$row->getTemplateType()];
        }

        return __($str);
    }
}
