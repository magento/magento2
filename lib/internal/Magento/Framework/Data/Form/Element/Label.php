<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Data form abstract class
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

class Label extends \Magento\Framework\Data\Form\Element\AbstractElement
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
        $this->setType('label');
    }

    /**
     * Retrieve Element HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getBold() ? '<div class="control-value special">' : '<div class="control-value">';
        $html .= $this->getEscapedValue() . '</div>';
        $html .= $this->getAfterElementHtml();
        return $html;
    }
}
