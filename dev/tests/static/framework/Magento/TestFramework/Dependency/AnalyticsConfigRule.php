<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Dependency;

/**
 * Class provides dependency rule for analytics.xml config file.
 */
class AnalyticsConfigRule implements RuleInterface
{
    /**
     * @inheritdoc
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents)
    {
        if ('config' != $fileType || !preg_match('#.*/analytics\.xml$#', $file)) {
            return [];
        }

        $dependenciesInfo = [];
        if (preg_match_all('#<[customProvider|reportProvider][^>]*class=[\'"]([^\'"]+)[\'"]#i', $contents, $matches)) {
            $classes = array_pop($matches);
            foreach ($classes as $class) {
                $classParts = explode('\\', $class);
                $module = implode('\\', array_slice($classParts, 0, 2));
                if (strtolower($currentModule) !== strtolower($module)) {
                    $dependenciesInfo[] = [
                        'module' => $module,
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => $file,
                    ];
                }
            }
        }

        return $dependenciesInfo;
    }
}
