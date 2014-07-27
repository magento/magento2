<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        array $data = array()
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
