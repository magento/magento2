<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;
use Magento\Mtf\Util\Command\Cli\DeployMode;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Verify visibility of form elements on Configuration page.
 *
 * @ZephyrId MAGETWO-71416
 */
class LoginAfterJSMinificationTest extends Injectable
{

    /**
     * Admin dashboard page
     * @var Dashboard
     */
    private $adminDashboardPage;

    /**
     * Factory for Test Steps.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Configuration setting.
     *
     * @var string
     */
    private $configData;

    /**
     * Prepare data for further test execution.
     *
     * @param Dashboard $adminDashboardPage
     * @return void
     */
    public function __inject(Dashboard $adminDashboardPage, TestStepFactory $stepFactory)
    {
        $this->adminDashboardPage = $adminDashboardPage;
        $this->stepFactory = $stepFactory;
    }

    public function test(DeployMode $cli, $configData = null)
    {
       //Pre-conditions
        $cli->setDeployModeToDeveloper();
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $cli->setDeployModeToProduction();
        $this->adminDashboardPage->open();
    }
}
