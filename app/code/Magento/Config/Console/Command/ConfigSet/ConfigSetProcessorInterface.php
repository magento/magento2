<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Console\Command\ConfigSet;

use Magento\Framework\Exception\CouldNotSaveException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Allows to process different flows of config:set command.
 *
 * @see \Magento\Config\Console\Command\ConfigSetCommand
 */
interface ConfigSetProcessorInterface
{
    /**
     * Processes config:set command.
     *
     * @param InputInterface $input An input console parameter
     * @return void
     * @throws CouldNotSaveException An exception on processing error
     */
    public function process(InputInterface $input);
}
