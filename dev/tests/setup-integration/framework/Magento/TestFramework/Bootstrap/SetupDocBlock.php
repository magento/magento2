<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Bootstrap;

/**
 * Bootstrap of the custom DocBlock annotations.
 *
 * \Magento\TestFramework\Isolation\DeploymentConfig was excluded for setup/upgrade tests.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SetupDocBlock extends \Magento\TestFramework\Bootstrap\DocBlock
{
    /**
     * Get list of subscribers. In addition, register <b>reinstallMagento</b> annotation processing.
     *
     * @param  \Magento\TestFramework\Application $application
     * @return array
     */
    protected function _getSubscribers(\Magento\TestFramework\Application $application)
    {
        return [
            new \Magento\TestFramework\Workaround\Segfault(),
            new \Magento\TestFramework\Workaround\Cleanup\TestCaseProperties(),
            new \Magento\TestFramework\Workaround\Cleanup\StaticProperties(),
            new \Magento\TestFramework\Isolation\WorkingDirectory(),
            new \Magento\TestFramework\Workaround\DeploymentConfig(),
            new \Magento\TestFramework\Annotation\ComponentRegistrarFixture($this->_fixturesBaseDir),
            new \Magento\TestFramework\Annotation\SchemaFixture($this->_fixturesBaseDir),
            new \Magento\TestFramework\Annotation\Cache(),
            new \Magento\TestFramework\Workaround\CacheClean(),
            new \Magento\TestFramework\Annotation\ReinstallInstance($application),
            new \Magento\TestFramework\Annotation\CopyModules(),
            new \Magento\TestFramework\Annotation\DataProviderFromFile()
        ];
    }
}
