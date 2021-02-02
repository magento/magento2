<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogWidget\Test\Unit\Controller\Adminhtml\Product\Widget;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ConditionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogWidget\Controller\Adminhtml\Product\Widget\Conditions
     */
    protected $controller;

    /**
     * @var \Magento\CatalogWidget\Model\Rule|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rule;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $response;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->rule = $this->createMock(\Magento\CatalogWidget\Model\Rule::class);
        $this->response = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->setMethods(['setBody', 'sendResponse'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->response->expects($this->once())->method('setBody')->willReturnSelf();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            \Magento\CatalogWidget\Controller\Adminhtml\Product\Widget\Conditions::class,
            [
                'rule' => $this->rule,
                'response' => $this->response
            ]
        );
        $this->request = $arguments['context']->getRequest();

        $this->objectManager = $arguments['context']->getObjectManager();
        $this->controller = $objectManagerHelper->getObject(
            \Magento\CatalogWidget\Controller\Adminhtml\Product\Widget\Conditions::class,
            $arguments
        );
    }

    public function testExecute()
    {
        $type = 'Magento\CatalogWidget\Model\Rule\Condition\Product|attribute_set_id';
        $this->request->expects($this->at(0))->method('getParam')->with('id')->willReturn('1--1');
        $this->request->expects($this->at(1))->method('getParam')->with('type')->willReturn($type);
        $this->request->expects($this->at(2))->method('getParam')->with('form')
            ->willReturn('request_form_param_value');

        $condition = $this->getMockBuilder(\Magento\CatalogWidget\Model\Rule\Condition\Product::class)
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
        $condition->expects($this->once())->method('setId')->with('1--1')->willReturnSelf();
        $condition->expects($this->once())->method('setType')
            ->with(\Magento\CatalogWidget\Model\Rule\Condition\Product::class)
            ->willReturnSelf();
        $condition->expects($this->once())->method('setRule')->with($this->rule)->willReturnSelf();
        $condition->expects($this->once())->method('setPrefix')->with('conditions')->willReturnSelf();
        $condition->expects($this->once())->method('setJsFormObject')->with('request_form_param_value')
            ->willReturnSelf();
        $condition->expects($this->once())->method('setAttribute')->with('attribute_set_id')->willReturnSelf();
        $condition->expects($this->once())->method('asHtmlRecursive')->willReturn('<some_html>');

        $this->objectManager->expects($this->once())->method('create')->willReturn($condition);

        $this->response->expects($this->once())->method('setBody')->with('<some_html>')->willReturnSelf();
        $this->controller->execute();
    }
}
