<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App\SensitiveConfigSet;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Interface for collection values from user input
 */
interface CollectorInterface
{
    /**
     * Collects values from user input and return result as array
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $configPaths list of available config paths
     * @return array for example
     *
     * ```php
     * [
     *     'some/configuration/path1' => 'someValue1',
     *     'some/configuration/path2' => 'someValue2',
     *     'some/configuration/path3' => 'someValue3',
     * ]
     * ```
     * @throws LocalizedException
     */
    public function getValues(InputInterface $input, OutputInterface $output, array $configPaths);
}
