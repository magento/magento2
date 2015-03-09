<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Unit\Controller\Adminhtml\Ajax;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SuggestTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Search\Controller\Ajax\Suggest */
    private $controller;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $response;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $request;

    /** @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $url;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    private $context;

    /** @var \Magento\Search\Model\AutocompleteInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $autocomplete;

    protected function setUp()
    {
        $this->autocomplete = $this->getMockBuilder('Magento\Search\Model\AutocompleteInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();
        $this->request = $this->getMockBuilder('\Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->response = $this->getMockBuilder('\Magento\Framework\App\ResponseInterface')
            ->disableOriginalConstructor()
            ->setMethods(['representJson', 'setRedirect'])
            ->getMockForAbstractClass();
        $this->url = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getBaseUrl'])
            ->getMockForAbstractClass();
        $this->context = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->setMethods(['getRequest', 'getResponse', 'getUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->atLeastOnce())
            ->method('getRequest')
            ->will($this->returnValue($this->request));
        $this->context->expects($this->atLeastOnce())
            ->method('getResponse')
            ->will($this->returnValue($this->response));
        $this->context->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($this->url));
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->controller = $this->objectManagerHelper->getObject(
            'Magento\Search\Controller\Ajax\Suggest',
            [
                'context' => $this->context,
                'autocomplete' => $this->autocomplete
            ]
        );
    }

    public function testExecute()
    {
        $searchString = "simple";
        $firstItemMock =  $this->getMockBuilder('Magento\Search\Model\Autocomplete\Item')
            ->disableOriginalConstructor()
            ->setMockClassName('FirstItem')
            ->setMethods(['toArray'])
            ->getMock();
        $secondItemMock =  $this->getMockBuilder('Magento\Search\Model\Autocomplete\Item')
            ->disableOriginalConstructor()
            ->setMockClassName('SecondItem')
            ->setMethods(['toArray'])
            ->getMock();

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('q')
            ->will($this->returnValue($searchString));

        $this->autocomplete->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue([$firstItemMock, $secondItemMock]));

        $this->response->expects($this->once())
            ->method('representJson');
        $this->controller->execute();
    }

    public function testExecuteEmptyQuery()
    {
        $searchString = "";

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('q')
            ->will($this->returnValue($searchString));
        $this->url->expects($this->once())
            ->method('getBaseUrl');
        $this->response->expects($this->once())
            ->method('setRedirect');
        $this->controller->execute();
    }
}
