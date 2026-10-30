<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Integration\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Parameters for new tokens.
 */
interface UserTokenParametersInterface extends ExtensibleDataInterface
{
    /**
     * Force issued timestamp as given.
     *
     * @return \DateTimeInterface|null
     */
    public function getForcedIssuedTime(): ?\DateTimeInterface;

    /**
     * @return \Magento\Integration\Api\Data\UserTokenParametersExtensionInterface|null
     */
    public function getExtensionAttributes(): ?UserTokenParametersExtensionInterface;

    /**
     * @param \Magento\Integration\Api\Data\UserTokenParametersExtensionInterface $extended
     */
    public function setExtensionAttributes(UserTokenParametersExtensionInterface $extended): void;
}
