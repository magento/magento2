<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework;

/**
 * Provides access to the application for the tests
 *
 * Allows installation and uninstallation
 */
class WebApiApplication extends Application
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        throw new \Exception(
            "Can't start application: purpose of Web API Application is to use classes and models from the application"
            . " and don't run it"
        );
    }

    /**
     * @inheritdoc
     */
    public function install($cleanup)
    {
        if ($cleanup) {
            $this->cleanup();
        }

        $installOptions = $this->getInstallConfig();

        /* Install application */
        if ($installOptions) {
            $installCmd = 'php -f ' . BP . '/bin/magento setup:install -vvv';
            $installArgs = [];
            foreach ($installOptions as $optionName => $optionValue) {
                if (is_bool($optionValue)) {
                    if (true === $optionValue) {
                        $installCmd .= " --$optionName";
                    }
                    continue;
                }
                $installCmd .= " --$optionName=%s";
                $installArgs[] = $optionValue;
            }
            $this->_shell->execute($installCmd, $installArgs);
        }
        /* Set Indexer mode as "Update on Save" & Reindex all the Indexers */
        $this->_shell->execute(
            'php -f ' . BP . '/bin/magento indexer:set-mode realtime -vvv'
        );
        $this->_shell->execute(
            'php -f ' . BP . '/bin/magento indexer:reindex -vvv'
        );

        $this->runPostInstallCommands();
    }

    /**
     * @inheritdoc
     *
     * Return empty array of custom directories
     * @return array
     */
    protected function getCustomDirs()
    {
        return [];
    }
}
