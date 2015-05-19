<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Exceptional situation of a performance testing scenario failure
 */
namespace Magento\TestFramework\Performance\Scenario;

use Magento\Framework\Phrase;

class FailureException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * @var \Magento\TestFramework\Performance\Scenario
     */
    protected $_scenario;

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\Performance\Scenario $scenario
     * @param Phrase $phrase
     */
    public function __construct(\Magento\TestFramework\Performance\Scenario $scenario, Phrase $phrase = null)
    {
        if ($phrase === null) {
            $phrase = new Phrase('Scenario failure.');
        }
        parent::__construct($phrase);
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
