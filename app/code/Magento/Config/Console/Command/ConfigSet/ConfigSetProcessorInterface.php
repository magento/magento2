<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface ConfigSetProcessorInterface.
 *
 * Allows to process different flows of config:set command.
 */
interface ConfigSetProcessorInterface
{
    /**
     * Processes config:set command.
     * Returns 0 on success and 1 otherwise.
     *
     * @param InputInterface $input An input parameter
     * @param OutputInterface $output An output parameter
     * @return int The code of operation, 0 on success or 1 on failure
     */
    public function process(InputInterface $input, OutputInterface $output);
}
