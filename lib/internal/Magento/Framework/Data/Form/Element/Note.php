<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/**
 * Form note element
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\Escaper;

class Note extends AbstractElement
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
        $this->setType('note');
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = $this->getBeforeElementHtml()
            . '<div id="'
            . $this->getHtmlId()
            . '" class="control-value">'
            . $this->getText()
            . '</div>'
            . $this->getAfterElementHtml();
        return $html;
    }
}
