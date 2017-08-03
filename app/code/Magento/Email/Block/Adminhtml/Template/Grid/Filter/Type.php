<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Block\Adminhtml\Template\Grid\Filter;

/**
 * Adminhtml system template grid type filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Type extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * Email template types
     *
     * @var array
     * @since 2.0.0
     */
    protected static $_types = [
        null => null,
        \Magento\Framework\App\TemplateTypesInterface::TYPE_HTML => 'HTML',
        \Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT => 'Text',
    ];

    /**
     * Get options
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getOptions()
    {
        $result = [];
        foreach (self::$_types as $code => $label) {
            $result[] = ['value' => $code, 'label' => __($label)];
        }

        return $result;
    }

    /**
     * Get condition
     *
     * @return array|null
     * @since 2.0.0
     */
    public function getCondition()
    {
        if ($this->getValue() === null) {
            return null;
        }

        return ['eq' => $this->getValue()];
    }
}
