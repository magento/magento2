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
namespace Magento\Core\App\Router;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\App\Router\Base
     */
    protected $_model;

    protected function setUp()
    {
        $options = array('routerId' => 'standard');
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\App\Router\Base',
            $options
        );
    }

    /**
     * @magentoAppArea frontend
     */
    public function testMatch()
    {
        if (!\Magento\TestFramework\Helper\Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test get match without sending headers');
        }

        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get('Magento\TestFramework\Request');

        $this->assertInstanceOf('Magento\Framework\App\ActionInterface', $this->_model->match($request));
        $request->setRequestUri('core/index/index');
        $this->assertInstanceOf('Magento\Framework\App\ActionInterface', $this->_model->match($request));

        $request->setPathInfo(
            'not_exists/not_exists/not_exists'
        )->setModuleName(
            'not_exists'
        )->setControllerName(
            'not_exists'
        )->setActionName(
            'not_exists'
        );
        $this->assertNull($this->_model->match($request));
    }

    public function testGetControllerClassName()
    {
        $this->assertEquals(
            'Magento\Core\Controller\Index',
            $this->_model->getActionClassName('Magento_Core', 'index')
        );
    }
}
