<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

/**
 * Rule Interface
 *
 * Interface for search path resolution during fallback process
 *
 * @api
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
