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
namespace Magento\Test;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Request
     */
    protected $_model = null;

    protected function setUp()
    {
        $this->_model = new \Magento\TestFramework\Request(
            $this->getMock('Magento\Framework\App\Route\ConfigInterface', [], [], '', false),
            $this->getMock('Magento\Framework\App\Request\PathInfoProcessorInterface', [], [], '', false),
            $this->getMock('Magento\Framework\Stdlib\CookieManager', [], [], '', false)
        );
    }

    public function testGetHttpHost()
    {
        $this->assertEquals('localhost', $this->_model->getHttpHost());
        $this->assertEquals('localhost', $this->_model->getHttpHost(false));
    }

    public function testSetGetServer()
    {
        $this->assertSame(array(), $this->_model->getServer());
        $this->assertSame($this->_model, $this->_model->setServer(array('test' => 'value', 'null' => null)));
        $this->assertSame(array('test' => 'value', 'null' => null), $this->_model->getServer());
        $this->assertEquals('value', $this->_model->getServer('test'));
        $this->assertSame(null, $this->_model->getServer('non-existing'));
        $this->assertSame('default', $this->_model->getServer('non-existing', 'default'));
        $this->assertSame(null, $this->_model->getServer('null'));
    }
}
