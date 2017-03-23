<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Form checkbox element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Checkbox extends AbstractElement
{
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
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
     */
    public function getIsChecked()
    {
        return $this->getData('checked');
    }
}
