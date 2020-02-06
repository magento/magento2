<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Query;

/**
 * @inheritdoc
 */
class ContextParameters implements ContextParametersInterface
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
     * @var array
     */
    private $extensionAttributesData = [];

    /**
     * @inheritdoc
     */
    public function setUserType(int $userType): void
    {
        $this->userType = $userType;
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
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
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
    public function addExtensionAttribute(string $key, $value): void
    {
        $this->extensionAttributesData[$key] = $value;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributesData(): array
    {
        return $this->extensionAttributesData;
    }
}
