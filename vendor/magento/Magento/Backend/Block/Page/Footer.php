<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Block\Page;

/**
 * Adminhtml footer block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Footer extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'page/footer.phtml';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setShowProfiler(true);
    }
}
