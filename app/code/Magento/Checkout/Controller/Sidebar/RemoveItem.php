<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Sidebar;

use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\ObjectManager;

/**
 * Class RemoveItem
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RemoveItem extends Action
{
    /**
     * @var Sidebar
     */
    protected $sidebar;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @param Context $context
     * @param Sidebar $sidebar
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @param PageFactory $resultPageFactory
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Sidebar $sidebar,
        LoggerInterface $logger,
        Data $jsonHelper,
        PageFactory $resultPageFactory
    ) {
        $this->sidebar = $sidebar;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Executes the main action of the controller
     *
     * @return $this
     */
    public function execute()
    {
        $itemId = (int)$this->getRequest()->getParam('item_id');
        try {
            if (!$this->getFormKeyValidator()->validate($this->getRequest())) {
                throw new LocalizedException(__('We can\'t remove the item.'));
            }
            $this->sidebar->checkQuoteItem($itemId);
            $this->sidebar->removeQuoteItem($itemId);
            return $this->jsonResponse();
        } catch (LocalizedException $e) {
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
     * @return Http
     */
    protected function jsonResponse($error = '')
    {
        $response = $this->sidebar->getResponseData($error);

        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    /**
     * Getter for FormKeyValidator
     *
     * @deprecated
     * @return Validator
     */
    private function getFormKeyValidator()
    {
        if ($this->formKeyValidator === null) {
            $this->formKeyValidator = ObjectManager::getInstance()->get(Validator::class);
        }
        return $this->formKeyValidator;
    }
}
