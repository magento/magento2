<?php

namespace Magento\ToolkitFramework;

abstract class Fixture
{
    /**
     * @var int
     */
    protected $priority;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @param Application $application
     */
    public function __construct(\Magento\ToolkitFramework\Application $application)
    {
        $this->application = $application;
    }

    abstract public function execute();

    abstract public function getActionTitle();

    abstract public function introduceParamLabels();

    public function getPriority()
    {
        return $this->priority;
    }
}