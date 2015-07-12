<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * {@inheritdoc}
     */
    public function run()
    {
        throw new \Exception(
            "Can't start application: purpose of Web API Application is to use classes and models from the application"
            . " and don't run it"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $installOptions = $this->getInstallConfig();

        /* Install application */
        if ($installOptions) {
            $installCmd = 'php -f ' . BP . '/bin/magento setup:install';
            $installArgs = [];
            foreach ($installOptions as $optionName => $optionValue) {
                if (is_bool($optionValue)) {
                    if (true === $optionValue) {
                        $installCmd .= " --$optionName";
                    }
                    continue;
                }
                if (!empty($optionValue)) {
                    $installCmd .= " --$optionName=%s";
                    $installArgs[] = $optionValue;
                }
            }
            $this->_shell->execute($installCmd, $installArgs);
        }
    }

    /**
     * Use the application as is
     *
     * {@inheritdoc}
     */
    protected function getCustomDirs()
    {
        return [];
    }
}
