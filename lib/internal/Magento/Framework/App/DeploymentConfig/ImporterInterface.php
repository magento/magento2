<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\DeploymentConfig;

interface ImporterInterface
{
    /**
     * Import data from shared configuration files to appropriate data storage
     *
     * @param array $data Data that should be imported
     * @return string[]
     * @throws \Magento\Framework\Exception\State\InvalidTransitionException In case of any exceptions
     * during importing. All changed during importing data is rolled back
     */
    public function import(array $data);
}