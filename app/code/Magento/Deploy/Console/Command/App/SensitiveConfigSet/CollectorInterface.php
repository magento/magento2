<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App\SensitiveConfigSet;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface CollectorInterface
 */
interface CollectorInterface
{
    /**
     * Collects values from user input and return result as array.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $configPaths list of available config paths.
     * @return array
     * @throws LocalizedException
     */
    public function getValues(InputInterface $input, OutputInterface $output, array $configPaths);
}
