<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class Fixture
{
    /**
     * @var int
     */
    protected $priority;

    /**
     * @var FixtureModel
     */
    protected $fixtureModel;

    /**
     * @param FixtureModel $fixtureModel
     */
    public function __construct(FixtureModel $fixtureModel)
    {
        $this->fixtureModel = $fixtureModel;
    }

    /**
     * Execute fixture
     *
     * @return void
     */
    abstract public function execute();

    /**
     * Get fixture action description
     *
     * @return string
     */
    abstract public function getActionTitle();

    /**
     * Print information about generated fixture. Print fixture label and amount of generated items
     *
     * @param OutputInterface $output
     * @return void
     */
    public function printInfo(OutputInterface $output)
    {
        foreach ($this->introduceParamLabels() as $configName => $label) {
            $configValue = $this->fixtureModel->getValue($configName);
            $generationCount = is_array($configValue) === true
                ? count($configValue[array_keys($configValue)[0]])
                : $configValue;

            if (!empty($generationCount)) {
                $output->writeln('<info> |- ' . $label . ': ' . $generationCount . '</info>');
            }
        }
    }

    /**
     * Introduce parameters labels
     *
     * @return array
     */
    abstract public function introduceParamLabels();

    /**
     * Get fixture priority
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }
}
