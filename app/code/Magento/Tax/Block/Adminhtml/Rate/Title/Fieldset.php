<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tax Rate Titles Fieldset
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Block\Adminhtml\Rate\Title;

/**
 * Class \Magento\Tax\Block\Adminhtml\Rate\Title\Fieldset
 *
 * @since 2.0.0
 */
class Fieldset extends \Magento\Framework\Data\Form\Element\Fieldset
{
    /**
     * @var \Magento\Tax\Block\Adminhtml\Rate\Title
     * @since 2.0.0
     */
    protected $_title;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Tax\Block\Adminhtml\Rate\Title $title
     * @param array $data
     * @since 2.0.0
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
     * @return string
     * @since 2.0.0
     */
    public function getBasicChildrenHtml()
    {
        return $this->_title->toHtml();
    }
}
