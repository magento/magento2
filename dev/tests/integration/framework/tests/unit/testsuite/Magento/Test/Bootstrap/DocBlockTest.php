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

/**
 * Test class for \Magento\TestFramework\Bootstrap\DocBlock.
 */
namespace Magento\Test\Bootstrap;

class DocBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Bootstrap\DocBlock
     */
    protected $_object;

    /**
     * @var \Magento\TestFramework\Application|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_application;

    protected function setUp()
    {
        $this->_object = new \Magento\TestFramework\Bootstrap\DocBlock(__DIR__);
        $this->_application = $this->getMock('Magento\TestFramework\Application', array(), array(), '', false);
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_application = null;
    }

    /**
     * Setup expectation of inability to instantiate an event listener without passing the event manager instance
     *
     * @param string $listenerClass
     * @param string $expectedExceptionMsg
     */
    protected function _expectNoListenerCreation($listenerClass, $expectedExceptionMsg)
    {
        try {
            new $listenerClass();
            $this->fail("Inability to instantiate the event listener '{$listenerClass}' is expected.");
        } catch (\Magento\Framework\Exception $e) {
            $this->assertEquals($expectedExceptionMsg, $e->getMessage());
        }
    }

    public function testRegisterAnnotations()
    {
        $this->_expectNoListenerCreation(
            'Magento\TestFramework\Event\PhpUnit',
            'Instance of the event manager is required.'
        );
        $this->_expectNoListenerCreation(
            'Magento\TestFramework\Event\Magento',
            'Instance of the "Magento\TestFramework\EventManager" is expected.'
        );
        $this->_object->registerAnnotations($this->_application);
        new \Magento\TestFramework\Event\PhpUnit();
        new \Magento\TestFramework\Event\Magento();
    }
}
