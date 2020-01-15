<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Product;

use Magento\Catalog\Controller\Product as ProductAction;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * View a product on storefront. Needs to be accessible by POST because of the store switching
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends ProductAction implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var ProductHelper\View
     */
    protected $viewHelper;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ProductHelper $productHelper
     * @param ProductHelper\View $viewHelper
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     * @param Json $serializer
     */
    public function __construct(
        Context $context,
        ProductHelper $productHelper,
        ProductHelper\View $viewHelper,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        Json $serializer
    ) {
        parent::__construct($context, $productHelper);
        $this->viewHelper = $viewHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * Redirect if product failed to load
     *
     * @return Redirect|Forward
     */
    protected function noProductRedirect()
    {
        $store = $this->getRequest()->getQuery('store');
        if (isset($store) && !$this->getResponse()->isRedirect()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('');
        }

        if (!$this->getResponse()->isRedirect()) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }

        return null;
    }

    /**
     * Product view action
     *
     * @return Forward|Redirect|Page
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
                    $this->serializer->jsonEncode(
                        [
                            'backUrl' => $this->_redirect->getRedirectUrl()
                        ]
                    )
                );
                return null;
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererOrBaseUrl();
            return $resultRedirect;
        }

        // Prepare helper and params
        $params = new DataObject();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        // Render page
        try {
            $page = $this->resultPageFactory->create();
            $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
            return $page;
        } catch (NoSuchEntityException $e) {
            return $this->noProductRedirect();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward('noroute');
        return $resultForward;
    }
}
