<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Log\Block\Adminhtml;

/**
 * Adminhtml online customers page content block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Online extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'online.phtml';

    /**
     * @return $this
     */
    public function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild('filterForm', 'Magento\Log\Block\Adminhtml\Online\Filter');
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getFilterFormHtml()
    {
        return $this->getChildBlock('filterForm')->toHtml();
    }
}
