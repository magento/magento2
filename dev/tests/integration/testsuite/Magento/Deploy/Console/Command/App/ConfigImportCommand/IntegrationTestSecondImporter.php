<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
