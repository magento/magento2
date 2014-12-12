<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Email\Block\Adminhtml\Template\Grid\Filter;

/**
 * Adminhtml system template grid type filter
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Type extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * Email template types
     *
     * @var array
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
     */
    public function getCondition()
    {
        if (is_null($this->getValue())) {
            return null;
        }

        return ['eq' => $this->getValue()];
    }
}
