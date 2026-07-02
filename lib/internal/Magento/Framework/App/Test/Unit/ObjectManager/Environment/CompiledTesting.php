<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\ObjectManager\Environment;

use Magento\Framework\App\ObjectManager\Environment\Compiled;
use Magento\Framework\Interception\ObjectManager\ConfigInterface;

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
     * @return ConfigInterface
     */
    public function getDiConfig()
    {
        return new ConfigTesting();
    }
}
