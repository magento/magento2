<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Form checkbox element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Checkbox extends AbstractElement
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('checkbox');
        $this->setExtType('checkbox');
    }

    /**
     * @return string[]
     * @since 2.0.0
     */
    public function getHtmlAttributes()
    {
        return [
            'type',
            'title',
            'class',
            'style',
            'checked',
            'onclick',
            'onchange',
            'disabled',
            'tabindex',
            'data-form-part',
            'data-role',
            'data-action'
        ];
    }

    /**
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
     */
    public function getElementHtml()
    {
        if ($checked = $this->getChecked()) {
            $this->setData('checked', true);
        } else {
            $this->unsetData('checked');
        }
        return parent::getElementHtml();
    }

    /**
     * Set check status of checkbox
     *
     * @param bool $value
     * @return Checkbox
     * @since 2.0.0
     */
    public function setIsChecked($value = false)
    {
        $this->setData('checked', $value);
        return $this;
    }

    /**
     * Return check status of checkbox
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsChecked()
    {
        return $this->getData('checked');
    }
}
