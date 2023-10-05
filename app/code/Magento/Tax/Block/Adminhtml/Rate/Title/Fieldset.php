<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax Rate Titles Fieldset
 */
namespace Magento\Tax\Block\Adminhtml\Rate\Title;

class Fieldset extends \Magento\Framework\Data\Form\Element\Fieldset
{
    /**
     * @var \Magento\Tax\Block\Adminhtml\Rate\Title
     */
    protected $_title;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Tax\Block\Adminhtml\Rate\Title $title
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Tax\Block\Adminhtml\Rate\Title $title,
        $data = []
    ) {
        $this->_title = $title;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Get title formatted in HTML
     *
     * @return string
     */
    public function getBasicChildrenHtml()
    {
        return $this->_title->toHtml();
    }
}
