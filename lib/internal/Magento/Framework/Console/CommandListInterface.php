<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console;

/**
 * Contains a list of Console commands
 */
interface CommandListInterface
{
    /**
     * Gets list of command instances
     *
     * @return \Symfony\Component\Console\Command\Command[]
     */
    public function getCommands();
}
