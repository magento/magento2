<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Controller\Ajax;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory as ResultRedirectFactory;
use Magento\Framework\UrlInterface;
use Magento\Search\Model\AutocompleteInterface;

/**
 * Ajax search autocomplete
 */
class Suggest implements HttpGetActionInterface
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
    private $resultRedirectFactory;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var AutocompleteInterface
     */
    private $autocomplete;

    /**
     * @param RequestInterface $request
     * @param ResultJsonFactory $resultJsonFactory
     * @param ResultRedirectFactory $resultRedirectFactory
     * @param UrlInterface $url
     * @param AutocompleteInterface $autocomplete
     */
    public function __construct(
        RequestInterface $request,
        ResultJsonFactory $resultJsonFactory,
        ResultRedirectFactory $resultRedirectFactory,
        UrlInterface $url,
        AutocompleteInterface $autocomplete
    ) {
        $this->request = $request;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->url = $url;
        $this->autocomplete = $autocomplete;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        if (!$this->request->getParam('q', false)) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($this->url->getBaseUrl());

            return $resultRedirect;
        }

        $autocompleteData = $this->autocomplete->getItems();
        $responseData = [];

        foreach ($autocompleteData as $resultItem) {
            $responseData[] = $resultItem->toArray();
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($responseData);

        return $resultJson;
    }
}
