<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Cache\Type\ConfigSegment;

class MassActionTest extends \Magento\Backend\Utility\Controller
{
    /**
     * Configuration segment of cache types
     *
     * @var ConfigSegment
     */
    private static $typesSegment;

    public static function setUpBeforeClass()
    {
        /** @var \Magento\Framework\App\DeploymentConfig $config */
        $config = Bootstrap::getObjectManager()->get('Magento\Framework\App\DeploymentConfig');
        $data = $config->getSegment(ConfigSegment::SEGMENT_KEY);
        self::$typesSegment = new ConfigSegment($data);
    }

    protected function tearDown()
    {
        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = Bootstrap::getObjectManager()->get('Magento\Framework\App\Cache\StateInterface');
        foreach (self::$typesSegment->getData() as $type => $value) {
            $cacheState->setEnabled($type, $value);
            $cacheState->persist();
        }
        parent::tearDown();
    }

    /**
     * @dataProvider massActionsDataProvider
     * @param array $typesToEnable
     */
    public function testMassEnableAction($typesToEnable = [])
    {
        $this->setAll(false);

        $this->getRequest()->setParams(['types' => $typesToEnable]);
        $this->dispatch('backend/admin/cache/massEnable');

        Bootstrap::getInstance()->reinitialize();

        /** @var  \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList */
        $cacheTypeList = Bootstrap::getObjectManager()->get('Magento\Framework\App\Cache\TypeListInterface');
        $types = array_keys($cacheTypeList->getTypes());
        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = Bootstrap::getObjectManager()->get('Magento\Framework\App\Cache\StateInterface');
        foreach ($types as $type) {
            if (in_array($type, $typesToEnable)) {
                $this->assertTrue($cacheState->isEnabled($type), "Type '{$type}' has not been enabled");
            } else {
                $this->assertFalse($cacheState->isEnabled($type), "Type '{$type}' must remain disabled");
            }
        }
    }

    /**
     * @dataProvider massActionsDataProvider
     * @param array $typesToDisable
     */
    public function testMassDisableAction($typesToDisable = [])
    {
        $this->setAll(true);

        $this->getRequest()->setParams(['types' => $typesToDisable]);
        $this->dispatch('backend/admin/cache/massDisable');

        Bootstrap::getInstance()->reinitialize();

        /** @var  \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList */
        $cacheTypeList = Bootstrap::getObjectManager()->get('Magento\Framework\App\Cache\TypeListInterface');
        $types = array_keys($cacheTypeList->getTypes());
        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = Bootstrap::getObjectManager()->get('Magento\Framework\App\Cache\StateInterface');
        foreach ($types as $type) {
            if (in_array($type, $typesToDisable)) {
                $this->assertFalse($cacheState->isEnabled($type), "Type '{$type}' has not been disabled");
            } else {
                $this->assertTrue($cacheState->isEnabled($type), "Type '{$type}' must remain enabled");
            }
        }
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
        $cacheState = Bootstrap::getObjectManager()->get('Magento\Framework\App\Cache\StateInterface');
        foreach (array_keys(self::$typesSegment->getData()) as $type) {
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
        $cacheTypeList = Bootstrap::getObjectManager()->get('Magento\Framework\App\Cache\TypeListInterface');
        $invalidatedTypes = array_keys($cacheTypeList->getInvalidated());
        $failed = array_intersect($typesToRefresh, $invalidatedTypes);
        $this->assertEmpty($failed, 'Could not refresh following cache types: ' . join(', ', $failed));
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
                    \Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER
                ]
            ]
        ];
    }
}
