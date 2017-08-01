<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Sidebar;

/**
 * Class \Magento\Checkout\Controller\Sidebar\RemoveItem
 *
 * @since 2.0.0
 */
class RemoveItem extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Sidebar
     * @since 2.0.0
     */
    protected $sidebar;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     * @since 2.0.0
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     * @since 2.1.0
     */
    private $formKeyValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Sidebar $sidebar
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute()
    {
        if (!$this->getFormKeyValidator()->validate($this->getRequest())) {
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
     * @since 2.0.0
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
     * @deprecated 2.1.0
     * @since 2.1.0
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
