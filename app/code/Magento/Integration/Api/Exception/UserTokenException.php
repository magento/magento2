<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Api\Exception;

use Magento\Authorization\Model\UserContextInterface;

/**
 * Describes user token related failure.
 */
class UserTokenException extends \InvalidArgumentException
{
    /**
     * @var UserContextInterface|null
     */
    private $context;

    public function __construct(
        string $message,
        ?\Throwable $previous = null,
        ?UserContextInterface $forContext = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->context = $forContext;
    }

    public function getUserContext(): ?UserContextInterface
    {
        return $this->context;
    }
}
