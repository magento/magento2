<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Model\UserToken;

use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Integration\Api\Data\UserTokenParametersInterface;
use Magento\Integration\Api\Data\UserTokenParametersExtensionInterface;

/**
 * @inheritDoc
 */
class UserTokenParameters implements UserTokenParametersInterface
{
    /**
     * @var \Magento\Framework\Api\ExtensionAttributesInterface|null|UserTokenParametersExtensionInterface
     */
    private $extensionAttrs;

    /**
     * @var \DateTimeInterface|null
     */
    private $forceIssued;

    public function __construct(ExtensionAttributesFactory $extensionAttrsFactory, ?\DateTimeInterface $issued = null)
    {
        $this->extensionAttrs = $extensionAttrsFactory->create(self::class, []);
        $this->forceIssued = $issued;

    }

    public function getForcedIssuedTime(): ?\DateTimeInterface
    {
        return $this->forceIssued;
    }

    public function getExtensionAttributes(): ?UserTokenParametersExtensionInterface
    {
        return $this->extensionAttrs;
    }

    public function setExtensionAttributes(UserTokenParametersExtensionInterface $extended): void
    {
        $this->extensionAttrs = $extended;
    }
}
