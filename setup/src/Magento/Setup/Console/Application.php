<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use \Magento\Framework\App\Bootstrap;

/**
 * Magento2 CLI Application
 *
 * {@inheritdoc}
 */
class Application extends SymfonyApplication
{
    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        foreach ($this->getApplicationCommands() as $command) {
            $commands[] = $this->add($command);
        }

        return $commands;
    }

    /**
     * Gets application commands
     *
     * @return array
     */
    protected function getApplicationCommands()
    {
        $commandsList = [];

        $serviceManager = \Zend\Mvc\Application::init(require BP . '/setup/config/application.config.php')->getServiceManager();
        $setupFiles = glob(BP . '/setup/src/Magento/Setup/Console/Command/*Command.php');
        if ($setupFiles) {
            foreach ($setupFiles as $file) {
                if (preg_match("#(Magento/Setup/Console/Command/.*Command).php#", $file, $parts)) {
                    $class = str_replace('/', '\\', $parts[1]);
                    $commandObject = null;
                    try {
                        $commandObject = $serviceManager->create($class);
                    } catch (\Exception $e) {
                        try {
                            echo "Could not create command using service manager: " . $e->getMessage() . "\n";
                            $commandObject = new $class();
                        } catch (\Exception $e) {
                        }
                    }
                    if (null !== $commandObject) {
                        $commandsList[] = $commandObject;
                    }
                }
            }
        }

        return $commandsList;
    }
}
