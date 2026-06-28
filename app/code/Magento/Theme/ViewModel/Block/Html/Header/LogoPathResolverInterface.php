<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
