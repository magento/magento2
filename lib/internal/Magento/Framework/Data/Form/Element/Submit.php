<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Form submit element
 */
class Submit extends AbstractElement
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
        $this->setExtType('submit');
        $this->setType('submit');
    }

    /**
     * Get HTML
     *
     * @return mixed
     */
    public function getHtml()
    {
        $this->addClass('submit');
        return parent::getHtml();
    }
}
