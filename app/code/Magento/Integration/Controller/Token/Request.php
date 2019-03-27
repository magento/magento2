<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Token;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Request extends Action implements CsrfAwareActionInterface
{
    /**
     * @var  \Magento\Framework\Oauth\OauthInterface
     */
    protected $oauthService;

    /**
     * @var  \Magento\Framework\Oauth\Helper\Request
     */
    protected $helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Oauth\OauthInterface $oauthService
     * @param \Magento\Framework\Oauth\Helper\Request $helper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Oauth\OauthInterface $oauthService,
        \Magento\Framework\Oauth\Helper\Request $helper
    ) {
        parent::__construct($context);
        $this->oauthService = $oauthService;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     *  Initiate RequestToken request operation
     *
     * @return void
     */
    public function execute()
    {
        try {
            $requestUrl = $this->helper->getRequestUrl($this->getRequest());
            $request = $this->helper->prepareRequest($this->getRequest(), $requestUrl);

            // Request request token
            $response = $this->oauthService->getRequestToken($request, $requestUrl, $this->getRequest()->getMethod());
        } catch (\Exception $exception) {
            $response = $this->helper->prepareErrorResponse($exception, $this->getResponse());
        }
        $this->getResponse()->setBody(http_build_query($response));
    }
}
