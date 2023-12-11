<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Controller\Sidebar;

use Exception;
use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Controller for removing quote item from shopping cart.
 */
class RemoveItem extends Action implements HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResultJsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ResultRedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var Sidebar
     */
    protected $sidebar;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param RequestInterface $request
     * @param ResultJsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param Sidebar $sidebar
     * @param Validator $formKeyValidator
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        ResultJsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        Sidebar $sidebar,
        Validator $formKeyValidator,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->sidebar = $sidebar;
        $this->formKeyValidator = $formKeyValidator;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            return $this->resultRedirectFactory->create()
                ->setPath('*/cart/');
        }

        $itemId = (int)$this->request->getParam('item_id');
        $error = '';

        try {
            $this->sidebar->checkQuoteItem($itemId);
            $this->sidebar->removeQuoteItem($itemId);
        } catch (LocalizedException $e) {
            $error = $e->getMessage();
        } catch (\Zend_Db_Exception $e) {
            $this->logger->critical($e);
            $error = __('An unspecified error occurred. Please contact us for assistance.');
        } catch (Exception $e) {
            $this->logger->critical($e);
            $error = $e->getMessage();
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($this->sidebar->getResponseData($error));

        return $resultJson;
    }
}
