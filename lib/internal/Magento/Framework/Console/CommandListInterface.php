<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console;

/**
 * Contains a list of Console commands
 * @api
 * @since 2.0.0
 */
interface CommandListInterface
{
    /**
     * Gets list of command instances
     *
     * @return \Symfony\Component\Console\Command\Command[]
     * @since 2.0.0
     */
    public function getCommands();
}
