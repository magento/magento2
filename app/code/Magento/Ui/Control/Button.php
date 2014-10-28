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
namespace Magento\Ui\Control;

use Magento\Framework\View\Element\Template;

/**
 * Class Button
 */
class Button extends Template
{
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('Magento_Ui::control/button/default.phtml');
        parent::_construct();
    }


    /**
     * Retrieve button type
     *
     * @return string
     */
    public function getType()
    {
        if (in_array($this->getData('type'), ['reset', 'submit'])) {
            return $this->getData('type');
        }

        return 'button';
    }

    /**
     * Retrieve onclick handler
     *
     * @return string
     */
    public function getOnClick()
    {
        $url = $this->hasData('url') ? $this->getData('url') : $this->getUrl();
        return sprintf("setLocation('%s');", $url);
    }

    /**
     * Retrieve attributes html
     *
     * @return string
     */
    public function getAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $title = $this->getTitle();
        if (empty($title)) {
            $title = $this->getLabel();
        }
        $classes = ['action-', 'scalable'];
        if ($this->hasData('class')) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }

        return $this->attributesToHtml($this->prepareAttributes($title, $classes, $disabled));
    }

    /**
     * Prepare attributes
     *
     * @param string $title
     * @param array $classes
     * @param string $disabled
     * @return array
     */
    protected function prepareAttributes($title, $classes, $disabled)
    {
        $attributes = [
            'id' => $this->getId(),
            'name' => $this->getElementName(),
            'title' => $title,
            'type' => $this->getType(),
            'class' => implode(' ', $classes),
            'onclick' => $this->getOnClick(),
            'style' => $this->getStyle(),
            'value' => $this->getValue(),
            'disabled' => $disabled
        ];
        if ($this->getDataAttribute()) {
            foreach ($this->getDataAttribute() as $key => $attr) {
                $attributes['data-' . $key] = is_scalar($attr) ? $attr : json_encode($attr);
            }
        }

        return $attributes;
    }

    /**
     * Attributes list to html
     *
     * @param array $attributes
     * @return string
     */
    protected function attributesToHtml($attributes)
    {
        $html = '';
        foreach ($attributes as $attributeKey => $attributeValue) {
            if ($attributeValue === null || $attributeValue == '') {
                continue;
            }
            $html .= $attributeKey . '="' . $this->escapeHtml($attributeValue) . '" ';
        }

        return $html;
    }
}
