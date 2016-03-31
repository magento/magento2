<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Test\Unit\Controller\Adminhtml\Product\Widget;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConditionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogWidget\Controller\Adminhtml\Product\Widget\Conditions
     */
    protected $controller;

    /**
     * @var \Magento\CatalogWidget\Model\Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rule;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->rule = $this->getMock('Magento\CatalogWidget\Model\Rule', [], [], '', false);
        $this->response = $this->getMockBuilder('\Magento\Framework\App\ResponseInterface')
            ->setMethods(['setBody', 'sendResponse'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->response->expects($this->once())->method('setBody')->will($this->returnSelf());

        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            'Magento\CatalogWidget\Controller\Adminhtml\Product\Widget\Conditions',
            [
                'rule' => $this->rule,
                'response' => $this->response
            ]
        );
        $this->request = $arguments['context']->getRequest();

        $this->objectManager = $arguments['context']->getObjectManager();
        $this->controller = $objectManagerHelper->getObject(
            'Magento\CatalogWidget\Controller\Adminhtml\Product\Widget\Conditions',
            $arguments
        );
    }

    public function testExecute()
    {
        $type = 'Magento\CatalogWidget\Model\Rule\Condition\Product|attribute_set_id';
        $this->request->expects($this->at(0))->method('getParam')->with('id')->will($this->returnValue('1--1'));
        $this->request->expects($this->at(1))->method('getParam')->with('type')->will($this->returnValue($type));
        $this->request->expects($this->at(2))->method('getParam')->with('form')
            ->will($this->returnValue('request_form_param_value'));

        $condition = $this->getMockBuilder('Magento\CatalogWidget\Model\Rule\Condition\Product')
            ->setMethods([
                'setId',
                'setType',
                'setRule',
                'setPrefix',
                'setAttribute',
                'asHtmlRecursive',
                'setJsFormObject',
            ])->disableOriginalConstructor()
            ->getMock();
        $condition->expects($this->once())->method('setId')->with('1--1')->will($this->returnSelf());
        $condition->expects($this->once())->method('setType')
            ->with('Magento\CatalogWidget\Model\Rule\Condition\Product')
            ->will($this->returnSelf());
        $condition->expects($this->once())->method('setRule')->with($this->rule)->will($this->returnSelf());
        $condition->expects($this->once())->method('setPrefix')->with('conditions')->will($this->returnSelf());
        $condition->expects($this->once())->method('setJsFormObject')->with('request_form_param_value')
            ->will($this->returnSelf());
        $condition->expects($this->once())->method('setAttribute')->with('attribute_set_id')->will($this->returnSelf());
        $condition->expects($this->once())->method('asHtmlRecursive')->will($this->returnValue('<some_html>'));

        $this->objectManager->expects($this->once())->method('create')->will($this->returnValue($condition));

        $this->response->expects($this->once())->method('setBody')->with('<some_html>')->will($this->returnSelf());
        $this->controller->execute();
    }
}
