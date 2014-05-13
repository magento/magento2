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
        return array(
            array(
                array('store_type' => 'website', 'store_data' => 'Magento\Store\Model\Website'),
                'Magento\Backend\Block\System\Store\Edit\Form\Website'
            ),
            array(
                array('store_type' => 'group', 'store_data' => 'Magento\Store\Model\Store'),
                'Magento\Backend\Block\System\Store\Edit\Form\Group'
            ),
            array(
                array('store_type' => 'store', 'store_data' => 'Magento\Store\Model\Store'),
                'Magento\Backend\Block\System\Store\Edit\Form\Store'
            )
        );
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
        return array(
            array(
                array(
                    'store_type' => 'website',
                    'store_data' => 'Magento\Store\Model\Website',
                    'store_action' => 'add'
                ),
                'New Web Site'
            ),
            array(
                array(
                    'store_type' => 'website',
                    'store_data' => 'Magento\Store\Model\Website',
                    'store_action' => 'edit'
                ),
                'Edit Web Site'
            ),
            array(
                array('store_type' => 'group', 'store_data' => 'Magento\Store\Model\Store', 'store_action' => 'add'),
                'New Store'
            ),
            array(
                array('store_type' => 'group', 'store_data' => 'Magento\Store\Model\Store', 'store_action' => 'edit'),
                'Edit Store'
            ),
            array(
                array('store_type' => 'store', 'store_data' => 'Magento\Store\Model\Store', 'store_action' => 'add'),
                'New Store View'
            ),
            array(
                array('store_type' => 'store', 'store_data' => 'Magento\Store\Model\Store', 'store_action' => 'edit'),
                'Edit Store View'
            )
        );
    }
}
