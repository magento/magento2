<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Deploy\Console\Command\App\ConfigImportCommand;

use Magento\Framework\App\DeploymentConfig\ImporterInterface;

class IntegrationTestSecondImporter implements ImporterInterface
{
    /**
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function import(array $data)
    {
        $messages[] = '<info>Integration second test data is imported!</info>';

        return $messages;
    }

    public function getWarningMessages(array $data)
    {
        return [];
    }
}
