<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml review main block
 */
namespace Magento\Review\Block\Adminhtml;

/**
 * Class \Magento\Review\Block\Adminhtml\Main
 *
 */
class Main extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Catalog product model factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Customer View Helper
     *
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Helper\View $customerViewHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->customerRepository = $customerRepository;
        $this->_productFactory = $productFactory;
        $this->_customerViewHelper = $customerViewHelper;
        parent::__construct($context, $data);
    }

    /**
     * Initialize add new review
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_addButtonLabel = __('New Review');
        parent::_construct();

        $this->_blockGroup = 'Magento_Review';
        $this->_controller = 'adminhtml';

        // lookup customer, if id is specified
        $customerId = $this->getRequest()->getParam('customerId', false);
        $customerName = '';
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $customerName = $this->escapeHtml($this->_customerViewHelper->getCustomerName($customer));
        }
        $productId = $this->getRequest()->getParam('productId', false);
        $productName = null;
        if ($productId) {
            $product = $this->_productFactory->create()->load($productId);
            $productName = $this->escapeHtml($product->getName());
        }

        if ($this->_coreRegistry->registry('usePendingFilter') === true) {
            if ($customerName) {
                $this->_headerText = __('Pending Reviews of Customer `%1`', $customerName);
            } else {
                $this->_headerText = __('Pending Reviews');
            }
            $this->buttonList->remove('add');
        } else {
            if ($customerName) {
                $this->_headerText = __('All Reviews of Customer `%1`', $customerName);
            } elseif ($productName) {
                $this->_headerText = __('All Reviews of Product `%1`', $productName);
            } else {
                $this->_headerText = __('All Reviews');
            }
        }
    }
}
