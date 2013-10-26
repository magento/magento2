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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\View\LayoutInterface');
        /** @var $headBlock \Magento\Page\Block\Html\Head */
        $headBlock = $layout->createBlock('Magento\Page\Block\Html\Head', 'head');
        $headBlock->setData('vde_design_mode', $designMode);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $page \Magento\Core\Model\Page */
        $page = $objectManager->get('Magento\Core\Model\Page');

        /** @var $pageAssets \Magento\Page\Model\Asset\GroupedCollection */
        $pageAssets = $page->getAssets();

        $fixtureAssets = array(
            array('name'   => 'test_css', 'type' => \Magento\Core\Model\View\Publisher::CONTENT_TYPE_CSS,
                  'params' => array()),
            array('name'   => 'test_css_vde', 'type' => \Magento\Core\Model\View\Publisher::CONTENT_TYPE_CSS,
                  'params' => array('flag_name' => 'vde_design_mode')),
            array('name'   => 'test_js', 'type' => \Magento\Core\Model\View\Publisher::CONTENT_TYPE_JS,
                  'params' => array()),
            array('name'   => 'test_js_vde', 'type' => \Magento\Core\Model\View\Publisher::CONTENT_TYPE_JS,
                  'params' => array('flag_name' => 'vde_design_mode')),
        );

        foreach ($fixtureAssets as $asset) {
            $pageAssets->add(
                $asset['name'],
                $objectManager->create('Magento\Core\Model\Page\Asset\ViewFile', array(
                    'file' => 'some_file',
                    'contentType' => $asset['type'],
                )),
                $asset['params']
            );
        }


        /** @var \Magento\Config\Scope $configScope */
        $configScope = $objectManager->get('Magento\Config\ScopeInterface');
        $configScope->setCurrentScope($area);

        /** @var $eventManager \Magento\Event\ManagerInterface */
        $eventManager = $objectManager->get('Magento\Event\ManagerInterface');
        $eventManager->dispatch('controller_action_layout_generate_blocks_after', array('layout' => $layout));

        $actualAssets = array_keys($pageAssets->getAll());
        $this->assertEquals($expectedAssets, $actualAssets);
    }

    /**
     * @return array
     */
    public function cleanJsDataProvider()
    {
        return array(
            'vde area - design mode' => array('vde', '1', array('test_css', 'test_css_vde', 'test_js_vde')),
            'vde area - non design mode' => array('vde', '0',
                array('test_css', 'test_css_vde', 'test_js', 'test_js_vde')),
            'default area - design mode' => array('default', '1',
                array('test_css', 'test_css_vde', 'test_js', 'test_js_vde')),
            'default area - non design mode' => array('default', '0',
                array('test_css', 'test_css_vde', 'test_js', 'test_js_vde')),
        );
    }
}
