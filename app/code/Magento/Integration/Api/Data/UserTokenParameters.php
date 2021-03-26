<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use \Magento\Framework\Api\ExtensionAttributesFactory;

/**
 * Parameters for new tokens.
 */
class UserTokenParameters implements ExtensibleDataInterface
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

    /**
     * Force issued timestamp as given.
     *
     * @return \DateTimeInterface|null
     */
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
