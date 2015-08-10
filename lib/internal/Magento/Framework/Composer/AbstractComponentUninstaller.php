<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Composer;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract class for a Component Uninstaller
 */
abstract class AbstractComponentUninstaller
{
    /**
     * Uninstall a component
     *
     * @param OutputInterface $ouput
     * @param array $components
     * @param array $option
     * @return void
     */
    abstract public function uninstall(OutputInterface $ouput, array $components, array $option);
}
