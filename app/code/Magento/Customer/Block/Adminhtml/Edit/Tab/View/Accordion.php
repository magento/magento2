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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Service\V1\Data\Customer;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Adminhtml customer recent orders grid block
 */
class Accordion extends \Magento\Backend\Block\Widget\Accordion
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Wishlist\Model\Resource\Item\CollectionFactory
     */
    protected $_itemsFactory;

    /** @var \Magento\Customer\Model\Config\Share  */
    protected $_shareConfig;

    /** @var CustomerAccountServiceInterface  */
    protected $_customerAccountService;

    /** @var \Magento\Customer\Service\V1\Data\CustomerBuilder  */
    protected $_customerBuilder;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Wishlist\Model\Resource\Item\CollectionFactory $itemsFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Config\Share $shareConfig
     * @param CustomerAccountServiceInterface $customerAccountService
     * @param \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Wishlist\Model\Resource\Item\CollectionFactory $itemsFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Config\Share $shareConfig,
        CustomerAccountServiceInterface $customerAccountService,
        \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder,
        array $data = array()
    ) {
        $this->_coreRegistry = $registry;
        $this->_quoteFactory = $quoteFactory;
        $this->_itemsFactory = $itemsFactory;
        $this->_shareConfig = $shareConfig;
        $this->_customerAccountService = $customerAccountService;
        $this->_customerBuilder = $customerBuilder;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->setId('customerViewAccordion');

        $this->addItem(
            'lastOrders',
            array(
                'title' => __('Recent Orders'),
                'ajax' => true,
                'content_url' => $this->getUrl('customer/*/lastOrders', array('_current' => true))
            )
        );

        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $customer = $this->getCustomer($customerId);
        $websiteIds = $this->_shareConfig->getSharedWebsiteIds($customer->getWebsiteId());
        // add shopping cart block of each website
        foreach ($websiteIds as $websiteId) {
            $website = $this->_storeManager->getWebsite($websiteId);

            // count cart items
            $cartItemsCount = $this->_quoteFactory->create()->setWebsite(
                $website
            )->loadByCustomer(
                $customerId
            )->getItemsCollection(
                false
            )->addFieldToFilter(
                'parent_item_id',
                array('null' => true)
            )->getSize();
            // prepare title for cart
            $title = __('Shopping Cart - %1 item(s)', $cartItemsCount);
            if (count($websiteIds) > 1) {
                $title = __('Shopping Cart of %1 - %2 item(s)', $website->getName(), $cartItemsCount);
            }

            // add cart ajax accordion
            $this->addItem(
                'shopingCart' . $websiteId,
                array(
                    'title' => $title,
                    'ajax' => true,
                    'content_url' => $this->getUrl(
                        'customer/*/viewCart',
                        array('_current' => true, 'website_id' => $websiteId)
                    )
                )
            );
        }

        // count wishlist items
        $wishlistCount = $this->_itemsFactory->create()->addCustomerIdFilter($customerId)->addStoreData()->getSize();
        // add wishlist ajax accordion
        $this->addItem(
            'wishlist',
            array(
                'title' => __('Wishlist - %1 item(s)', $wishlistCount),
                'ajax' => true,
                'content_url' => $this->getUrl('customer/*/viewWishlist', array('_current' => true))
            )
        );
    }

    /**
     * Get customer data from session or service.
     *
     * @param int|null $customerId possible customer ID from DB
     * @return Customer
     * @throws NoSuchEntityException
     */
    protected function getCustomer($customerId)
    {
        $customerData = $this->_backendSession->getCustomerData();
        if (!empty($customerData['account'])) {
            return $this->_customerBuilder->populateWithArray($customerData['account'])->create();
        } elseif ($customerId) {
            return $this->_customerAccountService->getCustomer($customerId);
        } else {
            return $this->_customerBuilder->create();
        }
    }
}
