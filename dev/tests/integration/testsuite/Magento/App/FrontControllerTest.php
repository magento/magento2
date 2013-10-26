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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\App;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FrontControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\App\FrontController
     */
    protected $_model;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->create('Magento\App\FrontController');
    }

    public function testSetGetDefault()
    {
        $this->_model->setDefault('test', 'value');
        $this->assertEquals('value', $this->_model->getDefault('test'));

        $default = array('some_key' => 'some_value');
        $this->_model->setDefault($default);
        $this->assertEquals($default, $this->_model->getDefault());
    }

    public function testGetRequest()
    {
        $this->assertNull($this->_model->getRequest());
    }

    public function testGetResponse()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')->setResponse(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\App\ResponseInterface')
        );
        if (!\Magento\TestFramework\Helper\Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test get response without sending headers');
        }
        $this->assertInstanceOf('Magento\App\ResponseInterface', $this->_model->getResponse());
    }

    public function testDispatch()
    {
        if (!\Magento\TestFramework\Helper\Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Cant\'t test dispatch process without sending headers');
        }
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->_objectManager->get('Magento\Config\Scope')->setCurrentScope('frontend');
        $request = $this->_objectManager->create('Magento\App\Request\Http');
        /* empty action */
        $request->setRequestUri('core/index/index');
        $this->_model->dispatch($request);
        $this->assertEmpty($this->_model->getResponse()->getBody());
        $this->assertEquals($request, $this->_model->getRequest());
    }
}
