<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\ViewModel\Block\Html\Header;

/**
 * Interface for resolving logo path
 */
interface LogoPathResolverInterface
{
    /**
     * Return logo image path
     *
     * @return null|string
     */
    public function getPath(): ?string;
}
