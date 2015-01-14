<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Block\Adminhtml;

/**
 * Adminhtml Google Content Items Grids Container
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Items extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @var string
     */
    protected $_template = 'items.phtml';

    /**
     * Flag factory
     *
     * @var \Magento\GoogleShopping\Model\FlagFactory
     */
    protected $_flagFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\GoogleShopping\Model\FlagFactory $flagFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\GoogleShopping\Model\FlagFactory $flagFactory,
        array $data = []
    ) {
        $this->_flagFactory = $flagFactory;
        parent::__construct($context, $data);
    }

    /**
     * Preparing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild('item', 'Magento\GoogleShopping\Block\Adminhtml\Items\Item');
        $this->addChild('product', 'Magento\GoogleShopping\Block\Adminhtml\Items\Product');
        //$this->addChild('store_switcher', 'Magento\GoogleShopping\Block\Adminhtml\Store\Switcher');

        return $this;
    }

    // /**
    //  * Get HTML code for Store Switcher select
    //  *
    //  * @return string
    //  */
    // public function getStoreSwitcherHtml()
    // {
    //     return $this->getChildHtml('store_switcher');
    // }

    /**
     * Get HTML code for CAPTCHA
     *
     * @return string
     */
    public function getCaptchaHtml()
    {
        return $this->getLayout()->createBlock(
            'Magento\GoogleShopping\Block\Adminhtml\Captcha'
        )->setGcontentCaptchaToken(
            $this->getGcontentCaptchaToken()
        )->setGcontentCaptchaUrl(
            $this->getGcontentCaptchaUrl()
        )->toHtml();
    }

    /**
     * Get selecetd store
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_getData('store');
    }

    /**
     * Check whether synchronization process is running
     *
     * @return bool
     */
    public function isProcessRunning()
    {
        $flag = $this->_flagFactory->create()->loadSelf();
        return $flag->isLocked();
    }

    /**
     * Build url for retrieving background process status
     *
     * @return string
     */
    public function getStatusUrl()
    {
        return $this->getUrl('adminhtml/*/status');
    }
}
