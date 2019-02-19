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
use Magento\User\Test\TestStep\LoginUserOnBackendStep;

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
    public function __inject(
        Dashboard $adminDashboardPage,
        TestStepFactory $stepFactory
    ) {
        $this->adminDashboardPage = $adminDashboardPage;
        $this->stepFactory = $stepFactory;
    }

    /**
     * Admin login test after JS minification is turned on in production mode.
     *
     * @param DeployMode $cli
     * @param null $configData
     *
     * @return void
     */
    public function test(
        DeployMode $cli,
        $configData = null
    ) {
        $this->configData = $configData;

        //Pre-conditions
        $cli->setDeployModeToDeveloper();
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        // Steps
        $cli->setDeployModeToProduction();
        $this->stepFactory->create(LoginUserOnBackendStep::class)->run();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->cleanup();
    }
}
