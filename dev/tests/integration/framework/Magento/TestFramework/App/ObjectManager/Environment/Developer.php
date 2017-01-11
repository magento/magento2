<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\App\ObjectManager\Environment;

class Developer extends \Magento\Framework\App\ObjectManager\Environment\Developer
{
    public function getDiConfig()
    {
        if (!$this->config) {
            $this->config = new \Magento\TestFramework\ObjectManager\Config(
                $this->envFactory->getRelations(),
                $this->envFactory->getDefinitions()
            );
        }

        return $this->config;
    }
}
