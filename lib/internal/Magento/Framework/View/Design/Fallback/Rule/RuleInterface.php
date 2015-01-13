<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

/**
 * Rule Interface
 *
 * Interface for search path resolution during fallback process
 */
interface RuleInterface
{
    /**
     * Get ordered list of folders to search for a file
     *
     * @param array $params Values to substitute placeholders with
     * @return array folders to perform a search
     */
    public function getPatternDirs(array $params);
}
