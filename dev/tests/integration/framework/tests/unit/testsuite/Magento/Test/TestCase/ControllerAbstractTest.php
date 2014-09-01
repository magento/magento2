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
namespace Magento\Test\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ControllerAbstractTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected $_bootstrap;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\Message\Manager */
    private $messageManager;

    protected function setUp()
    {
        $this->messageManager = $this->getMock('\Magento\Framework\Message\Manager', array(), array(), '', false);
        $request = new \Magento\TestFramework\Request(
            $this->getMock('Magento\Framework\App\Route\ConfigInterface', [], [], '', false),
            $this->getMock('Magento\Framework\App\Request\PathInfoProcessorInterface', [], [], '', false),
            $this->getMock('Magento\Framework\Stdlib\CookieManager', [], [], '', false)
        );
        $response = new \Magento\TestFramework\Response(
            $this->getMock('Magento\Framework\Stdlib\CookieManager', [], [], '', false),
            $this->getMock('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory', [], [], '', false),
            $this->getMock('Magento\Framework\App\Http\Context', [], [], '', false)
        );

        $this->_objectManager = $this->getMock(
            'Magento\TestFramework\ObjectManager',
            array('get', 'create'),
            array(),
            '',
            false
        );
        $this->_objectManager->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    array(
                        array('Magento\Framework\App\RequestInterface', $request),
                        array('Magento\Framework\App\ResponseInterface', $response),
                        array('Magento\Framework\Message\Manager', $this->messageManager),
                    )
                )
            );
    }

    /**
     * Bootstrap instance getter.
     * Mocking real bootstrap
     *
     * @return \Magento\TestFramework\Bootstrap
     */
    protected function _getBootstrap()
    {
        if (!$this->_bootstrap) {
            $this->_bootstrap = $this->getMock(
                'Magento\TestFramework\Bootstrap',
                array('getAllOptions'),
                array(),
                '',
                false
            );
        }
        return $this->_bootstrap;
    }

    public function testGetRequest()
    {
        $request = $this->getRequest();
        $this->assertInstanceOf('Magento\TestFramework\Request', $request);
    }

    public function testGetResponse()
    {
        $response = $this->getResponse();
        $this->assertInstanceOf('Magento\TestFramework\Response', $response);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testAssert404NotFound()
    {
        $this->getRequest()->setControllerName('noroute');
        $this->getResponse()->setBody(
            '404 Not Found test <h3>We are sorry, but the page you are looking for cannot be found.</h3>'
        );
        $this->assert404NotFound();

        $this->getResponse()->setBody('');
        try {
            $this->assert404NotFound();
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            return;
        }
        $this->fail('Failed response body validation');
    }

    /**
     * @expectedException \PHPUnit_Framework_AssertionFailedError
     */
    public function testAssertRedirectFailure()
    {
        $this->assertRedirect();
    }

    /**
     * @depends testAssertRedirectFailure
     */
    public function testAssertRedirect()
    {
        /*
         * Prevent calling \Magento\Framework\App\Response\Http::setRedirect() because it dispatches event,
         * which requires fully initialized application environment intentionally not available
         * for unit tests
         */
        $setRedirectMethod = new \ReflectionMethod('Zend_Controller_Response_Http', 'setRedirect');
        $setRedirectMethod->invoke($this->getResponse(), 'http://magentocommerce.com');
        $this->assertRedirect();
        $this->assertRedirect($this->equalTo('http://magentocommerce.com'));
    }

    /**
     * @param array $expectedMessages
     * @param string|null $messageTypeFilter
     * @dataProvider assertSessionMessagesDataProvider
     */
    public function testAssertSessionMessagesSuccess(array $expectedMessages, $messageTypeFilter)
    {
        $this->addSessionMessages();
        /** @var \PHPUnit_Framework_MockObject_MockObject|\PHPUnit_Framework_Constraint $constraint */
        $constraint = $this->getMock('PHPUnit_Framework_Constraint', array('toString', 'matches'));
        $constraint->expects(
            $this->once()
        )->method('matches')
            ->with($expectedMessages)
            ->will($this->returnValue(true));
        $this->assertSessionMessages($constraint, $messageTypeFilter);
    }

    public function assertSessionMessagesDataProvider()
    {
        return array(
            'message waning type filtering' => array(
                array('some_warning'),
                \Magento\Framework\Message\MessageInterface::TYPE_WARNING
            ),
            'message error type filtering' => array(
                array('error_one', 'error_two'),
                \Magento\Framework\Message\MessageInterface::TYPE_ERROR
            ),
            'message success type filtering'    => array(
                array('success!'),
                \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
            ),
        );
    }

    public function testAssertSessionMessagesAll()
    {
        $this->addSessionMessages();

        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'some_warning',
                    'error_one',
                    'error_two',
                    'some_notice',
                    'success!',
                ]
            )
        );
    }

    public function testAssertSessionMessagesEmpty()
    {
        $messagesCollection =  new \Magento\Framework\Message\Collection();
        $this->messageManager->expects($this->any())->method('getMessages')
            ->will($this->returnValue($messagesCollection));

        $this->assertSessionMessages($this->isEmpty());
    }

    private function addSessionMessages()
    {
        // emulate session messages
        $messagesCollection = new \Magento\Framework\Message\Collection();
        $messagesCollection
            ->addMessage(new \Magento\Framework\Message\Warning('some_warning'))
            ->addMessage(new \Magento\Framework\Message\Error('error_one'))
            ->addMessage(new \Magento\Framework\Message\Error('error_two'))
            ->addMessage(new \Magento\Framework\Message\Notice('some_notice'))
            ->addMessage(new \Magento\Framework\Message\Success('success!'));
        $this->messageManager->expects($this->any())->method('getMessages')
            ->will($this->returnValue($messagesCollection));
    }
}
