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

/**
 * Form element renderer to display link element
 *
 * @method array getValues()
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element;

class Links extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        $data = array()
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('links');
    }

    /**
     * Generates element html
     *
     * @return string
     */
    public function getElementHtml()
    {
        $values = $this->getValues();
        $links = array();
        if ($values) {
            foreach ($values as $option) {
                $links[] = $this->_optionToHtml($option);
            }
        }

        $html = sprintf(
            '<div id="%s" %s>%s%s</div><br />%s%s',
            $this->getHtmlId(),
            $this->serialize($this->getHtmlAttributes()),
            PHP_EOL,
            join('', $links),
            PHP_EOL,
            $this->getAfterElementHtml()
        );
        return $html;
    }

    /**
     * Generate list of links for element content
     *
     * @param array $option
     * @return string
     */
    protected function _optionToHtml(array $option)
    {
        $allowedAttribute = array('href', 'target', 'title', 'style');
        $attributes = array();
        foreach ($option as $title => $value) {
            if (!in_array($title, $allowedAttribute)) {
                continue;
            }
            $attributes[] = $title . '="' . $this->_escape($value) . '"';
        }

        $html = sprintf(
            '<a %s>%s</a>%s',
            join(' ', $attributes),
            $this->_escape($option['label']),
            isset($option['delimiter']) ? $option['delimiter'] : ''
        );

        return $html;
    }

    /**
     * Prepare array of anchor attributes
     *
     * @return string[]
     */
    public function getHtmlAttributes()
    {
        return array(
            'rel',
            'rev',
            'accesskey',
            'class',
            'style',
            'tabindex',
            'onmouseover',
            'title',
            'xml:lang',
            'onblur',
            'onclick',
            'ondblclick',
            'onfocus',
            'onmousedown',
            'onmousemove',
            'onmouseout',
            'onmouseup',
            'onkeydown',
            'onkeypress',
            'onkeyup'
        );
    }
}
