<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Integration\Api\Data;

use Magento\Authorization\Model\UserContextInterface;

/**
 * User token authentication.
 */
class UserToken
{
    /**
     * @var UserContextInterface
     */
    private $context;

    /**
     * @var UserTokenDataInterface
     */
    private $data;

    /**
     * @param UserContextInterface $context
     * @param UserTokenDataInterface $data
     */
    public function __construct(UserContextInterface $context, UserTokenDataInterface $data)
    {
        $this->context = $context;
        $this->data = $data;
    }

    public function getUserContext(): UserContextInterface
    {
        return $this->context;
    }

    public function getData(): UserTokenDataInterface
    {
        return $this->data;
    }
}
