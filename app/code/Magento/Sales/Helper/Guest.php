<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Helper;

use Magento\Framework\App as App;
use Magento\Framework\Exception\InputException;
use Magento\Sales\Model\Order;

/**
 * Sales module base helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Guest extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Cookie key for guest view
     */
    const COOKIE_NAME = 'guest-view';

    /**
     * Cookie path
     */
    const COOKIE_PATH = '/';

    /**
     * Cookie lifetime value
     */
    const COOKIE_LIFETIME = 600;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var string
     */
    private $inputExceptionMessage = 'You entered incorrect data. Please try again.';

    /**
     * @param App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository = null,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria = null
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->messageManager = $messageManager;
        $this->orderFactory = $orderFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderRepository = $orderRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $searchCriteria?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Api\SearchCriteriaBuilder::class);

        parent::__construct(
            $context
        );
    }

    /**
     * Try to load valid order by $_POST or $_COOKIE.
     *
     * @param App\RequestInterface $request
     * @return \Magento\Framework\Controller\Result\Redirect|bool
     */
    public function loadValidOrder(App\RequestInterface $request)
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('sales/order/history');
        }
        $post = $request->getPostValue();
        $fromCookie = $this->cookieManager->getCookie(self::COOKIE_NAME);

        if (empty($post) && !$fromCookie) {
            return $this->resultRedirectFactory->create()->setPath('sales/guest/form');
        }
        // It is unique place in the class that process exception and only InputException. It is need because by
        // input data we found order and one more InputException could be throws deeper in stack trace
        try {
            $order = (!empty($post) && isset($post['oar_order_id'], $post['oar_type']))
                ? $this->loadFromPost($post) : $this->loadFromCookie($fromCookie);
            $this->validateOrderStoreId($order->getStoreId());
            $this->coreRegistry->register('current_order', $order);

            return true;
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath('sales/guest/form');
        }
    }

    /**
     * Get Breadcrumbs for current controller action
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @return void
     */
    public function getBreadcrumbs(\Magento\Framework\View\Result\Page $resultPage)
    {
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()
            ]
        );
        $breadcrumbs->addCrumb(
            'cms_page',
            ['label' => __('Order Information'), 'title' => __('Order Information')]
        );
    }

    /**
     * Set guest-view cookie.
     *
     * @param string $cookieValue
     * @return void
     */
    private function setGuestViewCookie($cookieValue)
    {
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath(self::COOKIE_PATH)
            ->setHttpOnly(true);
        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $cookieValue, $metadata);
    }

    /**
     * Load order from cookie.
     *
     * @param string $fromCookie
     * @return Order
     * @throws InputException
     */
    private function loadFromCookie($fromCookie)
    {
        $cookieData = explode(':', base64_decode($fromCookie));
        $protectCode = isset($cookieData[0]) ? $cookieData[0] : null;
        $incrementId = isset($cookieData[1]) ? $cookieData[1] : null;
        if (!empty($protectCode) && !empty($incrementId)) {
            $order = $this->getOrderRecord($incrementId);
            if (hash_equals((string)$order->getProtectCode(), $protectCode)) {
                $this->setGuestViewCookie($fromCookie);

                return $order;
            }
        }
        throw new InputException(__($this->inputExceptionMessage));
    }

    /**
     * Load order data from post.
     *
     * @param array $postData
     * @return Order
     * @throws InputException
     */
    private function loadFromPost(array $postData)
    {
        if ($this->hasPostDataEmptyFields($postData)) {
            throw new InputException();
        }
        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->getOrderRecord($postData['oar_order_id']);
        if (!$this->compareStoredBillingDataWithInput($order, $postData)) {
            throw new InputException(__('You entered incorrect data. Please try again.'));
        }
        $toCookie = base64_encode($order->getProtectCode() . ':' . $postData['oar_order_id']);
        $this->setGuestViewCookie($toCookie);

        return $order;
    }

    /**
     * Check that billing data from the order and from the input are equal.
     *
     * @param Order $order
     * @param array $postData
     * @return bool
     */
    private function compareStoredBillingDataWithInput(Order $order, array $postData)
    {
        $type = $postData['oar_type'];
        $email = $postData['oar_email'];
        $lastName = $postData['oar_billing_lastname'];
        $zip = $postData['oar_zip'];
        $billingAddress = $order->getBillingAddress();

        return strtolower($lastName) === strtolower($billingAddress->getLastname())
            && ($type === 'email' && strtolower($email) === strtolower($billingAddress->getEmail())
            || $type === 'zip' && strtolower($zip) === strtolower($billingAddress->getPostcode()));
    }

    /**
     * Check post data for empty fields.
     *
     * @param array $postData
     * @return bool
     */
    private function hasPostDataEmptyFields(array $postData)
    {
        return empty($postData['oar_order_id']) || empty($postData['oar_billing_lastname'])
            || empty($postData['oar_type']) || empty($this->_storeManager->getStore()->getId())
            || !in_array($postData['oar_type'], ['email', 'zip'], true)
            || ('email' === $postData['oar_type'] && empty($postData['oar_email']))
            || ('zip' === $postData['oar_type'] && empty($postData['oar_zip']));
    }

    /**
     * Get order by increment_id and store_id.
     *
     * @param string $incrementId
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws InputException
     */
    private function getOrderRecord($incrementId)
    {
        $records = $this->orderRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter('increment_id', $incrementId)
                ->create()
        );
        if ($records->getTotalCount() < 1) {
            throw new InputException(__($this->inputExceptionMessage));
        }
        $items = $records->getItems();

        return array_shift($items);
    }

    /**
     * Check that store_id from order are equals with system.
     *
     * @param int $orderStoreId
     * @return void
     * @throws InputException
     */
    private function validateOrderStoreId($orderStoreId)
    {
        if ($orderStoreId != $this->_storeManager->getStore()->getId()) {
            throw new InputException(__($this->inputExceptionMessage));
        }
    }
}
