<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\CodingStandard\Tool;

/**
 * Defines an interface for compatibility checks against a specific version.
 */
interface CompatibilityInterface
{
    /**
     * Sets the version against which to test code.
     *
     * @param string $version
     * @return void
     */
    public function setTestVersion(string $version): void;
}
