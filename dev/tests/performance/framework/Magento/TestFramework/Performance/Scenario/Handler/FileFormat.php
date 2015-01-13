<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
    protected $_handlers = [];

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
