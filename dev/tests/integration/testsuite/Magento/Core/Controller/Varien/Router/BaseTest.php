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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Controller\Varien\Router;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Controller\Varien\Router\Base
     */
    protected $_model;

    protected function setUp()
    {
        $options = array(
            'areaCode' => 'frontend',
            'baseController' => 'Magento\Core\Controller\Front\Action',
            'routerId' => 'standard'
        );
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Controller\Varien\Router\Base', $options);
        $this->_model->setFront(\Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\App\FrontController'));
    }

    public function testFetchDefault()
    {
        $default = array(
            'module' => 'core',
            'controller' => 'index',
            'action' => 'index'
        );
        $this->assertEmpty($this->_model->getFront()->getDefault());
        $this->_model->fetchDefault();
        $this->assertEquals($default, $this->_model->getFront()->getDefault());
    }

    public function testMatch()
    {
        if (!\Magento\TestFramework\Helper\Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test get match without sending headers');
        }

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get('Magento\TestFramework\Request');

        $this->assertInstanceOf('Magento\Core\Controller\Varien\Action', $this->_model->match($request));
        $request->setRequestUri('core/index/index');
        $this->assertInstanceOf('Magento\Core\Controller\Varien\Action', $this->_model->match($request));

        $request->setPathInfo('not_exists/not_exists/not_exists')
            ->setModuleName('not_exists')
            ->setControllerName('not_exists')
            ->setActionName('not_exists');
        $this->assertNull($this->_model->match($request));
    }

    /**
     * @covers \Magento\Core\Controller\Varien\Router\Base::getModulesByFrontName
     * @covers \Magento\Core\Controller\Varien\Router\Base::getRouteByFrontName
     * @covers \Magento\Core\Controller\Varien\Router\Base::getFrontNameByRoute
     */
    public function testGetters()
    {
        $this->assertEquals(array('Magento_Catalog'), $this->_model->getModulesByFrontName('catalog'));
        $this->assertEquals('cms', $this->_model->getRouteByFrontName('cms'));
        $this->assertEquals('cms', $this->_model->getFrontNameByRoute('cms'));
    }

    public function testGetControllerClassName()
    {
        $this->assertEquals(
            'Magento\Core\Controller\Index',
            $this->_model->getControllerClassName('Magento_Core', 'index')
        );
    }
}
