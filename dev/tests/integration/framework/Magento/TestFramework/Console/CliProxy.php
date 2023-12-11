<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Console;

use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Provides the ability to inject additional DI configuration to call a CLI command
 */
class CliProxy implements \Magento\Framework\ObjectManager\NoninterceptableInterface
{
    /**
     * @var Cli
     */
    private $subject;

    /**
     * @param string $name
     * @param string $version
     * @throws \ReflectionException
     * @throws LocalizedException
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->subject = new Cli($name, $version);
        $this->injectDiConfiguration($this->subject);
    }

    /**
     * Runs the current application.
     *
     * @see \Magento\Framework\Console\Cli::doRun
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Exception
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        return $this->getSubject()->doRun($input, $output);
    }

    /**
     * Runs the current application.
     *
     * @see \Symfony\Component\Console\Application::run
     * @param InputInterface|null $input
     * @param OutputInterface|null $output
     * @return int
     * @throws \Exception
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return $this->getSubject()->run($input, $output);
    }

    /**
     * Get subject
     *
     * @return Cli
     */
    private function getSubject(): Cli
    {
        return $this->subject;
    }

    /**
     * Inject additional DI configuration
     *
     * @param Cli $cli
     * @return bool
     * @throws LocalizedException
     * @throws \ReflectionException
     */
    private function injectDiConfiguration(Cli $cli): bool
    {
        $diPreferences = $this->getDiPreferences();
        if ($diPreferences) {
            $object = new \ReflectionObject($cli);

            $attribute = $object->getProperty('objectManager');
            $attribute->setAccessible(true);

            /** @var ObjectManagerInterface $objectManager */
            $objectManager = $attribute->getValue($cli);
            $objectManager->configure($diPreferences);

            $attribute->setAccessible(false);
        }

        return true;
    }

    /**
     * Get additional DI preferences
     *
     * @return array|array[]
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getDiPreferences(): array
    {
        $diPreferences = [];
        $diPreferencesPath = $_SERVER['TESTS_BASE_DIR'] . '/etc/di/preferences/cli/';

        $preferenceFiles = glob($diPreferencesPath . '*.php');

        foreach ($preferenceFiles as $file) {
            if (!is_readable($file)) {
                throw new LocalizedException(__("'%1' is not readable file.", $file));
            }
            $diPreferences = array_replace($diPreferences, include $file);
        }

        return $diPreferences ? ['preferences' => $diPreferences] : $diPreferences;
    }
}
