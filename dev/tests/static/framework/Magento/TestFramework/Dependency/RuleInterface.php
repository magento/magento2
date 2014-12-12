<?php
/**
 * Rule for searching dependencies in layout files
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\TestFramework\Dependency;

interface RuleInterface
{
    /**
     * Types of dependencies between modules
     */
    const TYPE_SOFT = 'soft';

    const TYPE_HARD = 'hard';

    /**
     * Gets alien dependencies information for current module by analyzing file's contents
     *
     * @param string $currentModule
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return array
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents);
}
