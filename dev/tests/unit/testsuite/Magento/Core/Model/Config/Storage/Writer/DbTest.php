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
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Core\Model\Config\Storage\Writer\Db
 */
namespace Magento\Core\Model\Config\Storage\Writer;

class DbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\Storage\Writer\Db
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;


    protected function setUp()
    {
        $this->_resourceMock = $this->getMock('Magento\Core\Model\Resource\Config', array(), array(), '', false, false);
        $this->_model = new \Magento\Core\Model\Config\Storage\Writer\Db($this->_resourceMock);
    }

    protected function tearDown()
    {
        unset($this->_resourceMock);
        unset($this->_model);
    }

    public function testDelete()
    {
        $this->_resourceMock->expects($this->once())
            ->method('deleteConfig')
            ->with('test/path', 'store', 1);
        $this->_model->delete('test/path/', 'store', 1);
    }

    public function testDeleteWithDefaultParams()
    {
        $this->_resourceMock->expects($this->once())
            ->method('deleteConfig')
            ->with('test/path', \Magento\Core\Model\Store::DEFAULT_CODE, 0);
        $this->_model->delete('test/path');
    }

    public function testSave()
    {
        $this->_resourceMock->expects($this->once())
            ->method('saveConfig')
            ->with('test/path', 'test_value', 'store', 1);
        $this->_model->save('test/path/', 'test_value', 'store', 1);
    }

    public function testSaveWithDefaultParams()
    {
        $this->_resourceMock->expects($this->once())
            ->method('saveConfig')
            ->with('test/path', 'test_value', \Magento\Core\Model\Store::DEFAULT_CODE, 0);
        $this->_model->save('test/path', 'test_value');
    }
}
