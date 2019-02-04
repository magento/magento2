<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\App;

/**
 * Shell command line wrapper encapsulates command execution and arguments escaping
 */
class Shell extends \Magento\Framework\App\Shell
{
    /**
     * Override app/shell by running bin/magento located in the integration test and pass environment parameters
     *
     * @inheritdoc
     */
    public function execute($command, array $arguments = [])
    {
        if (strpos($command, BP . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento ') !== false) {
            $command = str_replace(
                BP . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento ',
                BP . DIRECTORY_SEPARATOR . 'dev' . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'integration'
                . DIRECTORY_SEPARATOR. 'bin' . DIRECTORY_SEPARATOR . 'magento ',
                $command
            );
        }

        $params = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams();

        $params['MAGE_DIRS']['base']['path'] = BP;
        $params = 'INTEGRATION_TEST_PARAMS="' . urldecode(http_build_query($params)) . '"';
        $integrationTestCommand = $params . ' ' . $command;
        $output = parent::execute($integrationTestCommand, $arguments);
        return $output;
    }
}
