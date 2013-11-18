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
 * @category   Magento
 * @package    Magento_Data
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Form editable select element
 *
 * Element allows inline modification of textual data within select
 *
 * @category   Magento
 * @package    Magento_Data
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Data\Form\Element;

class Editablemultiselect extends \Magento\Data\Form\Element\Multiselect
{
    /**
     * Name of the default JavaScript class that is used to make multiselect editable
     *
     * This class must define init() method and receive configuration in the constructor
     */
    const DEFAULT_ELEMENT_JS_CLASS = 'EditableMultiselect';

    /**
     * Retrieve HTML markup of the element
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = parent::getElementHtml();

        $selectConfig = $this->getData('select_config');
        if ($this->getData('disabled')) {
            $selectConfig['is_entity_editable'] = false;
        }

        $elementJsClass = self::DEFAULT_ELEMENT_JS_CLASS;
        if ($this->getData('element_js_class')) {
            $elementJsClass = $this->getData('element_js_class');
        }

        $selectConfigJson = \Zend_Json::encode($selectConfig);
        $jsObjectName = $this->getJsObjectName();
        $html .= '<script type="text/javascript">'
            . '/*<![CDATA[*/'
            . '(function($) { $().ready(function () { '
            . "var {$jsObjectName} = new {$elementJsClass}({$selectConfigJson}); "
            . "{$jsObjectName}.init(); }); })(jQuery);"
            . '/*]]>*/'
            . '</script>';
        return $html;
    }

    /**
     * Retrieve HTML markup of given select option
     *
     * @param array $option
     * @param array $selected
     * @return string
     */
    protected function _optionToHtml($option, $selected)
    {
        $html = '<option value="' . $this->_escape($option['value']) . '"';
        $html .= isset($option['title']) ? 'title="' . $this->_escape($option['title']) . '"' : '';
        $html .= isset($option['style']) ? 'style="' . $option['style'] . '"' : '';
        if (in_array((string)$option['value'], $selected)) {
            $html .= ' selected="selected"';
        }

        if ($this->getData('disabled')) {
            // if element is disabled then no data modification is allowed
            $html .= ' disabled="disabled" data-is-removable="no" data-is-editable="no"';
        }

        $html .= '>' . $this->_escape($option['label']) . '</option>' . "\n";
        return $html;
    }
}
