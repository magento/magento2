<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/**
 * Form password element
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Class Password
 *
 * Password input type
 */
class Password extends AbstractElement
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
        $this->setType('password');
        $this->setExtType('textfield');
    }

    /**
     * Get field html
     *
     * @return mixed
     */
    public function getHtml()
    {
        $this->addClass('input-text admin__control-text');

        return parent::getHtml();
    }
}
