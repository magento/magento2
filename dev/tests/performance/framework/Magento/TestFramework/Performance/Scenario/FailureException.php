<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Exceptional situation of a performance testing scenario failure
 */
namespace Magento\TestFramework\Performance\Scenario;

class FailureException extends \Magento\Framework\Exception
{
    /**
     * @var \Magento\TestFramework\Performance\Scenario
     */
    protected $_scenario;

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\Performance\Scenario $scenario
     * @param string $message
     */
    public function __construct(\Magento\TestFramework\Performance\Scenario $scenario, $message = '')
    {
        parent::__construct($message);
        $this->_scenario = $scenario;
    }

    /**
     * Retrieve scenario
     *
     * @return \Magento\TestFramework\Performance\Scenario
     */
    public function getScenario()
    {
        return $this->_scenario;
    }
}
