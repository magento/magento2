<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query;

/**
 * Concrete implementation for @see ContextInterface
 *
 * The purpose for this that GraphQL specification wants to make use of such object where multiple modules can
 * participate with data through extension attributes.
 */
class Context implements ContextInterface
{
    /**
     * @var int|null
     */
    private $userType;

    /**
     * @var int|null
     */
    private $userId;

    /**
     * @var ContextExtensionInterface
     */
    private $extensionAttributes;

    /**
     * @param int|null $userType
     * @param int|null $userId
     * @param ContextExtensionInterface $extensionAttributes
     */
    public function __construct(
        ?int $userType,
        ?int $userId,
        ContextExtensionInterface $extensionAttributes
    ) {
        $this->userType = $userType;
        $this->userId = $userId;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getUserType(): ?int
    {
        return $this->userType;
    }

    /**
     * @inheritdoc
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ContextExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
