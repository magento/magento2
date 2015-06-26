<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Setup\Console\Command\AbstractSetupCommand;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract class for jobs run by setup:cron:run command
 */
abstract class AbstractJob
{
    /**
     * @var AbstractSetupCommand
     */
    protected $command;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $params;


    /**
     * Constructor
     *
     * @param OutputInterface $output
     * @param Status $status
     * @param string $name
     * @param array $params
     */
    public function __construct(OutputInterface $output, Status $status, $name, array $params = [])
    {
        $this->output = $output;
        $this->status = $status;
        $this->name = $name;
        $this->params = $params;
    }

    /**
     * Get job name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get string representation of a job.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name . ' ' . json_encode($this->params);
    }

    /**
     * Execute job
     *
     * @return void
     */
    abstract public function execute();
}
