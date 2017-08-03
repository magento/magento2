<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form submit element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Class \Magento\Framework\Data\Form\Element\Submit
 *
 * @since 2.0.0
 */
class Submit extends AbstractElement
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
        $this->setExtType('submit');
        $this->setType('submit');
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getHtml()
    {
        $this->addClass('submit');
        return parent::getHtml();
    }
}
