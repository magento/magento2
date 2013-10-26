<?php
/**
 * Set of tests of layout directives handling behavior
 *
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
namespace Magento\Core\Model\DataService;

class LayoutTest extends \Magento\TestFramework\TestCase\AbstractController
{
    private $_dataServiceGraph;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        // Need to call this first so we get proper config
        $config = $this->_loadServiceCallsConfig();
        parent::setUp();
        $this->dispatch("catalog/category/view/foo/bar");
        $fixtureFileName = __DIR__ . DS . "LayoutTest" . DS . 'Magento' . DS . 'Catalog' . DS . 'Service'
            . DS . 'TestProduct.php';
        include $fixtureFileName;
        $invoker = $objectManager->create(
            'Magento\Core\Model\DataService\Invoker',
            array('config' => $config)
        );
        /** @var \Magento\Core\Model\DataService\Graph $dataServiceGraph */
        $this->_dataServiceGraph = $objectManager->create(
            'Magento\Core\Model\DataService\Graph',
            array('dataServiceInvoker' => $invoker)
        );
    }

    protected function _loadServiceCallsConfig()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Core\Model\Config\Modules\Reader $moduleReader */
        $moduleReader = $objectManager->create('Magento\Core\Model\Config\Modules\Reader');
        $moduleReader->setModuleDir('Magento_Catalog', 'etc', __DIR__ . '/LayoutTest/Magento/Catalog/etc');

        /** @var \Magento\Core\Model\DataService\Config\Reader\Factory $dsCfgReaderFactory */
        $dsCfgReaderFactory = $objectManager->create(
            'Magento\Core\Model\DataService\Config\Reader\Factory'
        );

        /** @var \Magento\Core\Model\DataService\Config $config */
        $dataServiceConfig = new \Magento\Core\Model\DataService\Config($dsCfgReaderFactory, $moduleReader);
        return $dataServiceConfig;
    }

    /**
     * Test Layout initialization of service calls
     */
    public function testServiceCalls()
    {
        /** @var \Magento\View\LayoutInterface $layout */
        $layout = $this->_getLayoutModel('layout_update.xml');
        $serviceCalls = $layout->getServiceCalls();
        $expectedServiceCalls = array(
            'testServiceCall' => array(
                'namespaces' => array(
                    'block_with_service_calls' => 'testData'
                )
            )
        );
        $this->assertEquals($expectedServiceCalls, $serviceCalls);
        $dictionary = $this->_dataServiceGraph->getByNamespace('block_with_service_calls');
        $expectedDictionary = array(
            'testData' => array(
                'testProduct' => array(
                    'id' => 'bar'
                )
            )
        );
        $this->assertEquals($expectedDictionary, $dictionary);
    }

    /**
     * Prepare a layout model with pre-loaded fixture of an update XML
     *
     * @param string $fixtureFile
     *
     * @return \Magento\View\LayoutInterface
     */
    protected function _getLayoutModel($fixtureFile)
    {
        /** @var $layout \Magento\Core\Model\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\View\LayoutInterface',
            array('dataServiceGraph' => $this->_dataServiceGraph)
        );
        $xml = simplexml_load_file(__DIR__ . "/LayoutTest/{$fixtureFile}", 'Magento\View\Layout\Element');
        $layout->setXml($xml);
        $layout->generateElements();
        return $layout;
    }
}
