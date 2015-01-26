<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Code standard tool wrapper interface
 */
namespace Magento\TestFramework\CodingStandard;

interface ToolInterface
{
    /**
     * Whether the tool can be ran on the current environment
     *
     * @return bool
     */
    public function canRun();

    /**
     * Run tool for files specified
     *
     * @param array $whiteList Files/directories to be inspected
     * @return int
     */
    public function run(array $whiteList);
}
