<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Transparent;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\Session\SessionManager;
use Magento\Paypal\Model\Payflow\Service\Request\SecureToken;
use Magento\Paypal\Model\Payflow\Transparent;

/**
 * Class RequestSecureToken
 *
 * @package Magento\Paypal\Controller\Transparent
 */
class RequestSecureToken extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Generic
     */
    private $sessionTransparent;

    /**
     * @var SecureToken
     */
    private $secureTokenService;

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * @var Transparent
     */
    private $transparent;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Generic $sessionTransparent
     * @param SecureToken $secureTokenService
     * @param SessionManager $sessionManager
     * @param Transparent $transparent
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Generic $sessionTransparent,
        SecureToken $secureTokenService,
        SessionManager $sessionManager,
        Transparent $transparent
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sessionTransparent = $sessionTransparent;
        $this->secureTokenService = $secureTokenService;
        $this->sessionManager = $sessionManager;
        $this->transparent = $transparent;
        parent::__construct($context);
    }

    /**
     * Send request to PayfloPro gateway for get Secure Token
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $this->sessionTransparent->setQuoteId($this->sessionManager->getQuote()->getId());

        $token = $this->secureTokenService->requestToken($this->sessionManager->getQuote());

        $result = [];
        $result[$this->transparent->getCode()]['fields'] = $token->getData();
        $result['success'] = $token->getSecuretoken() ? true : false;

        if (!$result['success']) {
            $result['error'] = true;
            $result['error_messages'] = __('Secure Token Error. Try again.');
        }

        return $this->resultJsonFactory->create()->setData($result);
    }
}
