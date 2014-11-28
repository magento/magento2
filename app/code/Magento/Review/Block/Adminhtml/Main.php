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

/**
 * Adminhtml review main block
 */
namespace Magento\Review\Block\Adminhtml;

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
        array $data = array()
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
        $this->_addButtonLabel = __('Add New Review');
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
