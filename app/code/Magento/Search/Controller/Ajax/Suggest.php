<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Ajax;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Search\Model\AutocompleteInterface;
use Magento\Framework\Controller\ResultFactory;

class Suggest extends Action implements HttpGetActionInterface
{
    /**
     * @param Context $context
     * @param AutocompleteInterface $autocomplete
     */
    public function __construct(
        Context $context,
        private readonly AutocompleteInterface $autocomplete
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('q', false)) {
            /** @var ResultRedirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getBaseUrl());
            return $resultRedirect;
        }

        $autocompleteData = $this->autocomplete->getItems();
        $responseData = [];
        foreach ($autocompleteData as $resultItem) {
            $responseData[] = $resultItem->toArray();
        }
        /** @var ResultJson $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }
}
