<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Controller\Product as ProductAction;

/**
 * View a product on storefront. Needs to be accessible by POST because of the store switching
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends ProductAction implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Catalog\Helper\Product\View
     */
    protected $viewHelper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Catalog\Helper\Product\View $viewHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product\View $viewHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger = null,
        \Magento\Framework\Json\Helper\Data $jsonHelper = null
    ) {
        parent::__construct($context);
        $this->viewHelper = $viewHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);
        $this->jsonHelper = $jsonHelper ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Json\Helper\Data::class);
    }

    /**
     * Redirect if product failed to load
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\Result\Forward
     */
    protected function noProductRedirect()
    {
        $store = $this->getRequest()->getQuery('store');
        if (isset($store) && !$this->getResponse()->isRedirect()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('');
        } elseif (!$this->getResponse()->isRedirect()) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }

    /**
     * Product view action
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        if ($this->getRequest()->isPost() && $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            $product = $this->_initProduct();

            if (!$product) {
                return $this->noProductRedirect();
            }

            if ($specifyOptions) {
                $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
                $this->messageManager->addNoticeMessage($notice);
            }

            if ($this->getRequest()->isAjax()) {
                $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode(
                        [
                            'backUrl' => $this->_redirect->getRedirectUrl()
                        ]
                    )
                );
                return;
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererOrBaseUrl();
            return $resultRedirect;
        }

        // Prepare helper and params
        $params = new \Magento\Framework\DataObject();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        // Render page
        try {
            $page = $this->resultPageFactory->create();
            $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
            return $page;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->noProductRedirect();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }
}
