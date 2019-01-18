<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Controller\Directpost\Payment;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 * Class Response
 * @package Magento\Authorizenet\Controller\Directpost\Payment
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method in July 2019
 */
class Response extends \Magento\Authorizenet\Controller\Directpost\Payment implements CsrfAwareActionInterface
{
    /**
     * @inheritDoc
     * @deprecated
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     * @deprecated
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Response action.
     * Action for Authorize.net SIM Relay Request.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @deprecated
     */
    public function execute()
    {
        $this->_responseAction('frontend');
        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
    }
}
