<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;

abstract class CardsManagement extends Action
{
    /**
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        protected readonly Session $customerSession
    ) {
        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }
}
