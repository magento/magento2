<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * Class \Magento\Sales\Controller\AbstractController\OrderLoader
 *
 * @since 2.0.0
 */
class OrderLoader implements OrderLoaderInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     * @since 2.0.0
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registry;

    /**
     * @var OrderViewAuthorizationInterface
     * @since 2.0.0
     */
    protected $orderAuthorization;

    /**
     * @var \Magento\Framework\UrlInterface
     * @since 2.0.0
     */
    protected $url;

    /**
     * @var ForwardFactory
     * @since 2.0.0
     */
    protected $resultForwardFactory;

    /**
     * @var RedirectFactory
     * @since 2.0.0
     */
    protected $redirectFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     * @param ForwardFactory $resultForwardFactory
     * @param RedirectFactory $redirectFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        OrderViewAuthorizationInterface $orderAuthorization,
        Registry $registry,
        \Magento\Framework\UrlInterface $url,
        ForwardFactory $resultForwardFactory,
        RedirectFactory $redirectFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderAuthorization = $orderAuthorization;
        $this->registry = $registry;
        $this->url = $url;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @param RequestInterface $request
     * @return bool|\Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     * @since 2.0.0
     */
    public function load(RequestInterface $request)
    {
        $orderId = (int)$request->getParam('order_id');
        if (!$orderId) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }

        $order = $this->orderFactory->create()->load($orderId);

        if ($this->orderAuthorization->canView($order)) {
            $this->registry->register('current_order', $order);
            return true;
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();
        return $resultRedirect->setUrl($this->url->getUrl('*/*/history'));
    }
}
