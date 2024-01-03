<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request\Backpressure;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Controller request context
 */
class ControllerContext implements ContextInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var string
     */
    private string $identity;

    /**
     * @var int
     */
    private int $identityType;

    /**
     * @var string
     */
    private string $typeId;

    /**
     * @var ActionInterface
     */
    private ActionInterface $action;

    /**
     * @param RequestInterface $request
     * @param string $identity
     * @param int $identityType
     * @param string $typeId
     * @param ActionInterface $action
     */
    public function __construct(
        RequestInterface $request,
        string $identity,
        int $identityType,
        string $typeId,
        ActionInterface $action
    ) {
        $this->request = $request;
        $this->identity = $identity;
        $this->identityType = $identityType;
        $this->typeId = $typeId;
        $this->action = $action;
    }

    /**
     * @inheritDoc
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * @inheritDoc
     */
    public function getIdentity(): string
    {
        return $this->identity;
    }

    /**
     * @inheritDoc
     */
    public function getIdentityType(): int
    {
        return $this->identityType;
    }

    /**
     * @inheritDoc
     */
    public function getTypeId(): string
    {
        return $this->typeId;
    }

    /**
     * Controller instance
     *
     * @return ActionInterface
     */
    public function getAction(): ActionInterface
    {
        return $this->action;
    }
}
