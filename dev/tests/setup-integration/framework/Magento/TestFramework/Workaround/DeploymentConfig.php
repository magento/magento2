<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Implementation of the @magentoSchemaFixture DocBlock annotation
 */
namespace Magento\TestFramework\Workaround;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Represents
 *
 * @magentoSchemaFixture {link_to_file.php}
 */
class DeploymentConfig
{
    /**
     * Apply magento data fixture on
     *
     * @return void
     */
    public function startTest()
    {
        /** @var \Magento\Framework\App\DeploymentConfig $deploymentConfig */
        $deploymentConfig = Bootstrap::getObjectManager()->get(\Magento\Framework\App\DeploymentConfig::class);
        $deploymentConfig->resetData();
    }
}
