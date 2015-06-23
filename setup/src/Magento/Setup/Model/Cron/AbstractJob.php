<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Magento\Setup\Console\Command\AbstractSetupCommand;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var Status
     */
    protected $status;

    /**
     * Constructor
     *
     * @param AbstractSetupCommand $command
     * @param OutputInterface $output
     * @param Status $status
     * @param $name
     * @param array $params
     */
    public function __construct(
        AbstractSetupCommand $command,
        OutputInterface $output,
        Status $status,
        $name,
        $params = []
    ) {
        $this->command = $command;
        $this->output = $output;
        $this->name = $name;
        $this->params = $params;
        $this->status = $status;
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
        return '<' . $this->name . '>' . json_encode($this->params);
    }

    abstract public function execute();
}