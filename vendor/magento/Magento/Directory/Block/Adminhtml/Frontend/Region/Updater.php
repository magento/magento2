<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Directory\Block\Adminhtml\Frontend\Region;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Updater extends \Magento\Backend\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        array $data = []
    ) {
        $this->_directoryHelper = $directoryHelper;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        $html .= "<script type=\"text/javascript\">var updater = new RegionUpdater('tax_defaults_country'," .
            " 'tax_region', 'tax_defaults_region', " .
            $this->_directoryHelper->getRegionJson() .
            ", 'disable');</script>";

        return $html;
    }
}
