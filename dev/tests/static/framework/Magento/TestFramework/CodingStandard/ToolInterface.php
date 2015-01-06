<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
