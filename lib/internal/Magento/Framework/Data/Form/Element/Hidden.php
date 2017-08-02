<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Form hidden element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

/**
 * Class \Magento\Framework\Data\Form\Element\Hidden
 *
 * @since 2.0.0
 */
class Hidden extends AbstractElement
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
        $this->setType('hidden');
        $this->setExtType('hiddenfield');
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getDefaultHtml()
    {
        $html = $this->getData('default_html');
        if ($html === null) {
            $html = $this->getElementHtml();
        }
        return $html;
    }
}
