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
namespace Magento\Framework\App\Action;

use Magento\TestFramework\Helper\ObjectManager;

/**
 * Test Forward
 *
 * getRequest,getResponse of AbstractAction class is also tested
 */
class ForwardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Action\Forward
     */
    protected $actionAbstract;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $cookieMetadataFactoryMock = $this->getMockBuilder(
            'Magento\Framework\Stdlib\Cookie\CookieMetadataFactory'
        )->disableOriginalConstructor()->getMock();
        $cookieManagerMock = $this->getMockBuilder('Magento\Framework\Stdlib\CookieManager')
            ->disableOriginalConstructor()->getMock();
        $contextMock = $this->getMockBuilder('Magento\Framework\App\Http\Context')->disableOriginalConstructor()
            ->getMock();
        $this->response = $objectManager->getObject(
            'Magento\Framework\App\Response\Http',
            [
                'cookieManager' => $cookieManagerMock,
                'cookieMetadataFactory' => $cookieMetadataFactoryMock,
                'context' => $contextMock
            ]
        );

        $this->request = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()->getMock();

        $this->actionAbstract = $objectManager->getObject(
            'Magento\Framework\App\Action\Forward',
            [
                'request' => $this->request,
                'response' => $this->response
            ]
        );
    }

    public function testDispatch()
    {
        $this->request->expects($this->once())->method('setDispatched')->with(false);
        $this->actionAbstract->dispatch($this->request);
    }

    /**
     * Test for getRequest method
     *
     * @test
     * @covers \Magento\Framework\App\Action\AbstractAction::getRequest
     */
    public function testGetRequest()
    {
        $this->assertSame($this->request, $this->actionAbstract->getRequest());
    }

    /**
     * Test for getResponse method
     *
     * @test
     * @covers \Magento\Framework\App\Action\AbstractAction::getResponse
     */
    public function testGetResponse()
    {
        $this->assertSame($this->response, $this->actionAbstract->getResponse());
    }

    /**
     * Test for getResponse med. Checks that response headers are set correctly
     *
     * @test
     * @covers \Magento\Framework\App\Action\AbstractAction::getResponse
     */
    public function testResponseHeaders()
    {
        $this->assertEmpty($this->actionAbstract->getResponse()->getHeaders());
    }
}
