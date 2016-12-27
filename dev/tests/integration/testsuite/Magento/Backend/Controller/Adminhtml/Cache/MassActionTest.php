<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\App\Cache\State;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\App\State as AppState;

class MassActionTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Configuration of cache types
     *
     * @var array
     */
    private static $typesConfig;

    /**
     * @var string
     */
    private $mageState;

    public static function setUpBeforeClass()
    {
        /** @var \Magento\Framework\App\DeploymentConfig $config */
        $config = Bootstrap::getObjectManager()->get(\Magento\Framework\App\DeploymentConfig::class);
        self::$typesConfig = $config->get(State::CACHE_KEY);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->mageState = Bootstrap::getObjectManager()->get(AppState::class)->getMode();
    }

    protected function tearDown()
    {
        Bootstrap::getObjectManager()->get(AppState::class)->setMode($this->mageState);
        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Cache\StateInterface::class);
        foreach (self::$typesConfig as $type => $value) {
            $cacheState->setEnabled($type, $value);
        }
        $cacheState->persist();
        parent::tearDown();
    }

    /**
     * @dataProvider massActionsDataProvider
     * @param array $typesToEnable
     */
    public function testMassEnableActionDeveloperMode($typesToEnable = [])
    {
        $this->setAll(false);

        $this->getRequest()->setParams(['types' => $typesToEnable]);
        $this->dispatch('backend/admin/cache/massEnable');

        foreach ($this->getCacheStates() as $cacheType => $cacheState) {
            if (in_array($cacheType, $typesToEnable)) {
                $this->assertEquals(1, $cacheState, "Type '{$cacheType}' has not been enabled");
            } else {
                $this->assertEquals(0, $cacheState, "Type '{$cacheType}' must remain disabled");
            }
        }
    }

    /**
     * @dataProvider massActionsDataProvider
     * @param array $typesToEnable
     */
    public function testMassEnableActionProductionMode($typesToEnable = [])
    {
        Bootstrap::getObjectManager()->get(AppState::class)->setMode(AppState::MODE_PRODUCTION);
        $this->setAll(false);

        $this->getRequest()->setParams(['types' => $typesToEnable]);
        $this->dispatch('backend/admin/cache/massEnable');

        foreach ($this->getCacheStates() as $cacheType => $cacheState) {
            $this->assertEquals(0, $cacheState, "Type '{$cacheType}' must remain disabled");
        }
    }

    /**
     * @dataProvider massActionsDataProvider
     * @param array $typesToDisable
     */
    public function testMassDisableActionDeveloperMode($typesToDisable = [])
    {
        $this->setAll(true);

        $this->getRequest()->setParams(['types' => $typesToDisable]);
        $this->dispatch('backend/admin/cache/massDisable');

        foreach ($this->getCacheStates() as $cacheType => $cacheState) {
            if (in_array($cacheType, $typesToDisable)) {
                $this->assertEquals(0, $cacheState, "Type '{$cacheType}' has not been disabled");
            } else {
                $this->assertEquals(1, $cacheState, "Type '{$cacheType}' must remain enabled");
            }
        }
    }

    /**
     * @dataProvider massActionsDataProvider
     * @param array $typesToDisable
     */
    public function testMassDisableActionProductionMode($typesToDisable = [])
    {
        Bootstrap::getObjectManager()->get(AppState::class)->setMode(AppState::MODE_PRODUCTION);
        $this->setAll(true);

        $this->getRequest()->setParams(['types' => $typesToDisable]);
        $this->dispatch('backend/admin/cache/massDisable');

        foreach ($this->getCacheStates() as $cacheType => $cacheState) {
            $this->assertEquals(1, $cacheState, "Type '{$cacheType}' must remain enabled");
        }
    }

    /**
     * Retrieve cache states (enabled/disabled) information
     *
     * Access configuration file directly as it is not possible to re-include modified file under HHVM
     * @link https://github.com/facebook/hhvm/issues/1447
     *
     * @return array
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    protected function getCacheStates()
    {
        $configFilePool = new ConfigFilePool();
        $configPath = Bootstrap::getInstance()->getAppTempDir() . '/'. DirectoryList::CONFIG .'/'
            . $configFilePool->getPath($configFilePool::APP_ENV);
        $configData = eval(str_replace('<?php', '', file_get_contents($configPath)));
        return $configData[State::CACHE_KEY];
    }

    /**
     * Sets all cache types to enabled or disabled state
     *
     * @param bool $isEnabled
     * @return void
     */
    private function setAll($isEnabled)
    {
        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Cache\StateInterface::class);
        foreach (array_keys(self::$typesConfig) as $type) {
            $cacheState->setEnabled($type, $isEnabled);
        }
        $cacheState->persist();
    }

    /**
     * @magentoDataFixture Magento/Backend/controllers/_files/cache/all_types_invalidated.php
     * @dataProvider massActionsDataProvider
     * @param array $typesToRefresh
     */
    public function testMassRefreshAction($typesToRefresh = [])
    {
        $this->getRequest()->setParams(['types' => $typesToRefresh]);
        $this->dispatch('backend/admin/cache/massRefresh');

        /** @var $cacheTypeList \Magento\Framework\App\Cache\TypeListInterface */
        $cacheTypeList = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Cache\TypeListInterface::class);
        $invalidatedTypes = array_keys($cacheTypeList->getInvalidated());
        $failed = array_intersect($typesToRefresh, $invalidatedTypes);
        $this->assertEmpty($failed, 'Could not refresh following cache types: ' . implode(', ', $failed));
    }

    /**
     * @return array
     */
    public function massActionsDataProvider()
    {
        return [
            'no types' => [[]],
            'existing types' => [
                [
                    \Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER,
                    \Magento\Framework\App\Cache\Type\Layout::TYPE_IDENTIFIER,
                    \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER,
                ],
            ]
        ];
    }
}
