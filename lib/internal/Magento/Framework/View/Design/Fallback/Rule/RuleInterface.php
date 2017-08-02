<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

/**
 * Rule Interface
 *
 * Interface for search path resolution during fallback process
 * @since 2.0.0
 */
interface RuleInterface
{
    /**
     * Get ordered list of folders to search for a file
     *
     * @param array $params Values to substitute placeholders with
     * @return array folders to perform a search
     * @since 2.0.0
     */
    public function getPatternDirs(array $params);
}
