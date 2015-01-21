<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Interface for handling performance testing scenarios
 */
namespace Magento\TestFramework\Performance\Scenario;

interface HandlerInterface
{
    /**
     * Run scenario and optionally write results to report file
     *
     * @param \Magento\TestFramework\Performance\Scenario $scenario
     * @param string|null $reportFile Report file to write results to, NULL disables report creation
     */
    public function run(\Magento\TestFramework\Performance\Scenario $scenario, $reportFile = null);
}
