<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for \Magento\DesignEditor\Model\Observer
 */
namespace Magento\DesignEditor\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $area
     * @param string $designMode
     * @param array $expectedAssets
     *
     * @magentoAppIsolation enabled
     * @dataProvider cleanJsDataProvider
     */
    public function testCleanJs($area, $designMode, $expectedAssets)
    {
        /** @var \Magento\Framework\Registry $registry */
        $registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Registry'
        );
        $registry->register('vde_design_mode', $designMode);

        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\View\Asset\Repository $assetRepo */
        $assetRepo = $objectManager->create('Magento\Framework\View\Asset\Repository');

        /** @var $pageAssets \Magento\Framework\View\Asset\GroupedCollection */
        $pageAssets = $objectManager->get('Magento\Framework\View\Asset\GroupedCollection');

        $fixtureAssets = [
            ['file' => 'test.css', 'params' => []],
            ['file' => 'test_vde.css', 'params' => ['flag_name' => 'vde_design_mode']],
            ['file' => 'test.js', 'params' => []],
            ['file' => 'test_vde.js', 'params' => ['flag_name' => 'vde_design_mode']],
        ];

        foreach ($fixtureAssets as $asset) {
            $pageAssets->add(
                $asset['file'],
                $assetRepo->createAsset($asset['file']),
                $asset['params']
            );
        }

        /** @var \Magento\Framework\Config\Scope $configScope */
        $configScope = $objectManager->get('Magento\Framework\Config\ScopeInterface');
        $configScope->setCurrentScope($area);

        /** @var $eventManager \Magento\Framework\Event\ManagerInterface */
        $eventManager = $objectManager->get('Magento\Framework\Event\ManagerInterface');
        $eventManager->dispatch('layout_generate_blocks_after', ['layout' => $layout]);

        $actualAssets = array_keys($pageAssets->getAll());
        $this->assertEquals($expectedAssets, $actualAssets);
    }

    /**
     * @return array
     */
    public function cleanJsDataProvider()
    {
        return [
            'vde area - design mode' => ['vde', '1', ['test.css', 'test_vde.css', 'test_vde.js']],
            'vde area - non design mode' => ['vde', '0',
                ['test.css', 'test_vde.css', 'test.js', 'test_vde.js'], ],
            'default area - design mode' => ['default', '1',
                ['test.css', 'test_vde.css', 'test.js', 'test_vde.js'], ],
            'default area - non design mode' => ['default', '0',
                ['test.css', 'test_vde.css', 'test.js', 'test_vde.js'], ],
        ];
    }
}
