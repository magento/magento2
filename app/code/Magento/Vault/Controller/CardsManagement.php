<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class \Magento\Vault\Controller\CardsManagement
 *
 * @since 2.1.0
 */
abstract class CardsManagement extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     * @since 2.1.0
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @since 2.1.0
     */
    public function __construct(
        Context $context,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     * @since 2.1.0
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }
}
