<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\ToolkitFramework;

/**
 * Class Fixture
 * @package Magento\ToolkitFramework
 */
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
