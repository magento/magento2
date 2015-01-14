<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store;

/**
 * @magentoAppArea adminhtml
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\Registry')->unregister('store_type');
        $objectManager->get('Magento\Framework\Registry')->unregister('store_data');
        $objectManager->get('Magento\Framework\Registry')->unregister('store_action');
    }

    /**
     * @param $registryData
     */
    protected function _initStoreTypesInRegistry($registryData)
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        foreach ($registryData as $key => $value) {
            if ($key == 'store_data') {
                $value = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($value);
            }
            $objectManager->get('Magento\Framework\Registry')->register($key, $value);
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @param $registryData
     * @param $expected
     * @dataProvider getStoreTypesForLayout
     */
    public function testStoreTypeFormCreated($registryData, $expected)
    {
        $this->_initStoreTypesInRegistry($registryData);

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\Backend\Block\System\Store\Edit */
        $block = $layout->createBlock('Magento\Backend\Block\System\Store\Edit', 'block');
        $block->setArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $this->assertInstanceOf($expected, $block->getChildBlock('form'));
    }

    /**
     * @return array
     */
    public function getStoreTypesForLayout()
    {
        return [
            [
                ['store_type' => 'website', 'store_data' => 'Magento\Store\Model\Website'],
                'Magento\Backend\Block\System\Store\Edit\Form\Website',
            ],
            [
                ['store_type' => 'group', 'store_data' => 'Magento\Store\Model\Store'],
                'Magento\Backend\Block\System\Store\Edit\Form\Group'
            ],
            [
                ['store_type' => 'store', 'store_data' => 'Magento\Store\Model\Store'],
                'Magento\Backend\Block\System\Store\Edit\Form\Store'
            ]
        ];
    }

    /**
     * @magentoAppIsolation enabled
     * @param $registryData
     * @param $expected
     * @dataProvider getStoreDataForBlock
     */
    public function testGetHeaderText($registryData, $expected)
    {
        $this->_initStoreTypesInRegistry($registryData);

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\Backend\Block\System\Store\Edit */
        $block = $layout->createBlock('Magento\Backend\Block\System\Store\Edit', 'block');
        $block->setArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $this->assertEquals($expected, $block->getHeaderText());
    }

    /**
     * @return array
     */
    public function getStoreDataForBlock()
    {
        return [
            [
                [
                    'store_type' => 'website',
                    'store_data' => 'Magento\Store\Model\Website',
                    'store_action' => 'add',
                ],
                'New Web Site',
            ],
            [
                [
                    'store_type' => 'website',
                    'store_data' => 'Magento\Store\Model\Website',
                    'store_action' => 'edit',
                ],
                'Edit Web Site'
            ],
            [
                ['store_type' => 'group', 'store_data' => 'Magento\Store\Model\Store', 'store_action' => 'add'],
                'New Store'
            ],
            [
                ['store_type' => 'group', 'store_data' => 'Magento\Store\Model\Store', 'store_action' => 'edit'],
                'Edit Store'
            ],
            [
                ['store_type' => 'store', 'store_data' => 'Magento\Store\Model\Store', 'store_action' => 'add'],
                'New Store View'
            ],
            [
                ['store_type' => 'store', 'store_data' => 'Magento\Store\Model\Store', 'store_action' => 'edit'],
                'Edit Store View'
            ]
        ];
    }
}
