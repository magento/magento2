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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Handler delegates execution to one of registered scenario handlers depending on a scenario file extension
 */
namespace Magento\TestFramework\Performance\Scenario\Handler;

class FileFormat implements \Magento\TestFramework\Performance\Scenario\HandlerInterface
{
    /**
     * @var array
     */
    protected $_handlers = array();

    /**
     * Register scenario handler to process scenario files with a certain extension
     *
     * @param string $fileExtension
     * @param \Magento\TestFramework\Performance\Scenario\HandlerInterface $handlerInstance
     * @return \Magento\TestFramework\Performance\Scenario\Handler\FileFormat
     */
    public function register(
        $fileExtension,
        \Magento\TestFramework\Performance\Scenario\HandlerInterface $handlerInstance
    ) {
        $this->_handlers[$fileExtension] = $handlerInstance;
        return $this;
    }

    /**
     * Retrieve scenario handler for a file extension
     *
     * @param string $fileExtension
     * @return \Magento\TestFramework\Performance\Scenario\HandlerInterface|null
     */
    public function getHandler($fileExtension)
    {
        return isset($this->_handlers[$fileExtension]) ? $this->_handlers[$fileExtension] : null;
    }

    /**
     * Run scenario and optionally write results to report file
     *
     * @param \Magento\TestFramework\Performance\Scenario $scenario
     * @param string|null $reportFile Report file to write results to, NULL disables report creation
     * @throws \Magento\Framework\Exception
     */
    public function run(\Magento\TestFramework\Performance\Scenario $scenario, $reportFile = null)
    {
        $scenarioExtension = pathinfo($scenario->getFile(), PATHINFO_EXTENSION);
        /** @var $scenarioHandler \Magento\TestFramework\Performance\Scenario\HandlerInterface */
        $scenarioHandler = $this->getHandler($scenarioExtension);
        if (!$scenarioHandler) {
            throw new \Magento\Framework\Exception(
                "Unable to run scenario '{$scenario->getTitle()}', format is not supported."
            );
        }
        $scenarioHandler->run($scenario, $reportFile);
    }
}
