<?php

namespace Example\ExampleFrontendUi\Controller\Input;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Message input result controller
 * @package Example\ExampleFrontendUi\Controller\Input
 */
class Result extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     *
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $message = $this->getRequest()->getParam('message');

        $result = $this->jsonFactory->create();
        $resultPage = $this->pageFactory->create();

        $block = $resultPage->getLayout()
            ->createBlock('Example\ExampleFrontendUi\Block\Input\Index')
            ->setTemplate('Example_ExampleFrontendUi::example_input.phtml')
            ->setData('message', $message)
            ->toHtml();

        $result->setData(['output' => $block]);
        return $result;
    }
}
