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
namespace Magento\Persistent\Model;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Session
     */
    protected $_model;

    /**
     * @var \Magento\Session\Config\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \Magento\Stdlib\Cookie|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cookieMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_configMock = $this->getMock('Magento\Session\Config\ConfigInterface');
        $this->_cookieMock = $this->getMock('Magento\Stdlib\Cookie', array(), array(), '', false);
        $resourceMock = $this->getMockForAbstractClass('Magento\Model\Resource\Db\AbstractDb',
            array(), '', false, false, true,
            array('__wakeup', 'getIdFieldName', 'getConnection', 'beginTransaction', 'delete', 'commit', 'rollBack'));

        $appStateMock = $this->getMock('Magento\App\State', array(), array(), '', false);
        $eventDispatcherMock = $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false, false);
        $cacheManagerMock = $this->getMock('Magento\App\CacheInterface', array(), array(), '', false, false);
        $loggerMock = $this->getMock('Magento\Logger', array(), array(), '', false);
        $actionValidatorMock = $this->getMock(
            '\Magento\Model\ActionValidator\RemoveAction', array(), array(), '', false
        );
        $actionValidatorMock->expects($this->any())->method('isAllowed')->will($this->returnValue(true));

        $context = new \Magento\Model\Context(
            $loggerMock, $eventDispatcherMock, $cacheManagerMock, $appStateMock, $actionValidatorMock
        );

        $this->_model = $helper->getObject('Magento\Persistent\Model\Session', array(
            'sessionConfig' => $this->_configMock,
            'cookie'        => $this->_cookieMock,
            'resource'      => $resourceMock,
            'context'       => $context
        ));
    }

    /**
     * @covers \Magento\Persistent\Model\Session::_afterDeleteCommit
     * @covers \Magento\Persistent\Model\Session::removePersistentCookie
     */
    public function testAfterDeleteCommit()
    {
        $cookiePath = 'some_path';
        $this->_configMock->expects($this->once())->method('getCookiePath')->will($this->returnValue($cookiePath));
        $this->_cookieMock->expects(
            $this->once()
        )->method(
            'set'
        )->with(
            \Magento\Persistent\Model\Session::COOKIE_NAME,
            $this->anything(),
            $this->anything(),
            $cookiePath
        );
        $this->_model->delete();
    }
}
