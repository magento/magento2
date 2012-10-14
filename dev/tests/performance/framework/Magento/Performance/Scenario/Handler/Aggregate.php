<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     performance_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Handler aggregating multiple performance scenario handlers
 */
class Magento_Performance_Scenario_Handler_Aggregate implements Magento_Performance_Scenario_HandlerInterface
{
    /**
     * @var array
     */
    protected $_handlers = array();

    /**
     * Constructor
     *
     * @param array $handlers Instances of Magento_Performance_Scenario_HandlerInterface
     * @throws InvalidArgumentException
     */
    public function __construct(array $handlers)
    {
        if (empty($handlers)) {
            throw new InvalidArgumentException('At least one scenario handler must be defined.');
        }
        foreach ($handlers as $oneScenarioHandler) {
            if (!($oneScenarioHandler instanceof Magento_Performance_Scenario_HandlerInterface)) {
                throw new InvalidArgumentException(
                    'Scenario handler must implement "Magento_Performance_Scenario_HandlerInterface".'
                );
            }
        }
        $this->_handlers = $handlers;
    }

    /**
     * Run scenario and optionally write results to report file
     *
     * @param string $scenarioFile
     * @param Magento_Performance_Scenario_Arguments $scenarioArguments
     * @param string|null $reportFile Report file to write results to, NULL disables report creation
     * @return bool Whether handler was able to process scenario
     */
    public function run($scenarioFile, Magento_Performance_Scenario_Arguments $scenarioArguments, $reportFile = null)
    {
        foreach ($this->_handlers as $oneScenarioHandler) {
            /** @var $oneScenarioHandler Magento_Performance_Scenario_HandlerInterface */
            if ($oneScenarioHandler->run($scenarioFile, $scenarioArguments, $reportFile)) {
                /* Stop execution upon first handling */
                return true;
            }
        }
        return false;
    }
}
