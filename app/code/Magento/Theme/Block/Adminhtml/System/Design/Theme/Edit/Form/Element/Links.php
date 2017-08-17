<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form element renderer to display link element
 *
 * @method array getValues()
 */
namespace Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element;

/**
 * Class \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form\Element\Links
 *
 */
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
        $data = []
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
        $links = [];
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
        $allowedAttribute = ['href', 'target', 'title', 'style'];
        $attributes = [];
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
        return [
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
            'onkeyup',
            'data-role',
            'data-action'
        ];
    }
}
