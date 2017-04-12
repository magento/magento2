<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\ObjectManager\Environment;

use Magento\Framework\App\ObjectManager\Environment\Compiled;

class CompiledTesting extends Compiled
{
    /**
     * @return array
     */
    protected function getConfigData()
    {
        return [];
    }

    /**
     * @return \Magento\Framework\Interception\ObjectManager\ConfigInterface
     */
    public function getDiConfig()
    {
        return new ConfigTesting();
    }
}
