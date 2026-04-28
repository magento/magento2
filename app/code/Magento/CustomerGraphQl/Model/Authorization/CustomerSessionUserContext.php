<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Authorization;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Model\Authorization\CustomerSessionUserContext as BaseCustomerSessionUserContext;
use Magento\Customer\Model\Session\Proxy as CustomerSessionProxy;
use Magento\Framework\App\Request\Http;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * GraphQL customer user context that keeps cacheable GET requests stateless.
 */
class CustomerSessionUserContext extends BaseCustomerSessionUserContext implements ResetAfterRequestInterface
{
    /**
     * @var Http
     */
    private Http $request;

    /**
     * @var int|null
     */
    private ?int $userId = null;

    /**
     * @var bool
     */
    private bool $isUserIdResolved = false;

    /**
     * @param CustomerSessionProxy $customerSession
     * @param Http $request
     */
    public function __construct(
        CustomerSessionProxy $customerSession,
        Http $request
    ) {
        parent::__construct($customerSession);
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function getUserId()
    {
        return $this->resolveUserId();
    }

    /**
     * @inheritDoc
     */
    public function getUserType()
    {
        return $this->resolveUserId() !== null ? UserContextInterface::USER_TYPE_CUSTOMER : null;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->userId = null;
        $this->isUserIdResolved = false;
    }

    /**
     * @return int|null
     */
    private function resolveUserId(): ?int
    {
        if ($this->isUserIdResolved) {
            return $this->userId;
        }

        $this->isUserIdResolved = true;
        if ($this->request->isGet()) {
            return null;
        }

        $userId = parent::getUserId();
        $this->userId = $userId !== null ? (int) $userId : null;

        return $this->userId;
    }
}
