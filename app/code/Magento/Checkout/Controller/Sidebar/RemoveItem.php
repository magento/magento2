<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Sidebar;

class RemoveItem extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Sidebar
     */
    protected $sidebar;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Sidebar $sidebar
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Sidebar $sidebar,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->sidebar = $sidebar;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost() || !$this->getFormKeyValidator()->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/cart/');
        }
        $itemId = (int)$this->getRequest()->getParam('item_id');
        try {
            $this->sidebar->checkQuoteItem($itemId);
            $this->sidebar->removeQuoteItem($itemId);
            return $this->jsonResponse();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Compile JSON response
     *
     * @param string $error
     * @return \Magento\Framework\App\Response\Http
     */
    protected function jsonResponse($error = '')
    {
        $response = $this->sidebar->getResponseData($error);

        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    /**
     * @return \Magento\Framework\Data\Form\FormKey\Validator
     * @deprecated
     */
    private function getFormKeyValidator()
    {
        if (!$this->formKeyValidator) {
            $this->formKeyValidator = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Data\Form\FormKey\Validator::class);
        }
        return $this->formKeyValidator;
    }
}
