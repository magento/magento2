<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\PageCache\Block\System\Config\Form\Field;

class StubExport extends \Magento\PageCache\Block\System\Config\Form\Field\Export
{
    /**
     * Disable parent constructor
     */
    public function __construct()
    {
    }

    public function setUrlBuilder(\Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function getElementHtml($element)
    {
        return $this->_getElementHtml($element);
    }
}
