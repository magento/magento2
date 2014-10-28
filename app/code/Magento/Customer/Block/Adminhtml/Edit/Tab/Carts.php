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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

/**
 * Obtain all carts contents for specified client
 */
class Carts extends \Magento\Backend\Block\Template
{
    /** @var \Magento\Customer\Model\Config\Share */
    protected $_shareConfig;

    /**
     * @var \Magento\Customer\Service\V1\Data\CustomerBuilder
     */
    protected $_customerBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context          $context
     * @param \Magento\Customer\Model\Config\Share             $shareConfig
     * @param \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Customer\Model\Config\Share $shareConfig,
        \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder,
        array $data = array()
    ) {
        $this->_shareConfig = $shareConfig;
        $this->_customerBuilder = $customerBuilder;
        parent::__construct($context, $data);
    }

    /**
     * Add shopping cart grid of each website
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $sharedWebsiteIds = $this->_shareConfig->getSharedWebsiteIds($this->_getCustomer()->getWebsiteId());
        $isShared = count($sharedWebsiteIds) > 1;
        foreach ($sharedWebsiteIds as $websiteId) {
            $blockName = 'customer_cart_' . $websiteId;
            $block = $this->getLayout()->createBlock(
                'Magento\Customer\Block\Adminhtml\Edit\Tab\Cart',
                $blockName,
                array('data' => array('website_id' => $websiteId))
            );
            if ($isShared) {
                $websiteName = $this->_storeManager->getWebsite($websiteId)->getName();
                $block->setCartHeader(__('Shopping Cart from %1', $websiteName));
            }
            $this->setChild($blockName, $block);
        }
        return parent::_prepareLayout();
    }

    /**
     * Just get child blocks html
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('adminhtml_block_html_before', array('block' => $this));
        return $this->getChildHtml();
    }

    /**
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    protected function _getCustomer()
    {
        return $this->_customerBuilder->populateWithArray(
            $this->_backendSession->getCustomerData()['account']
        )->create();
    }
}
