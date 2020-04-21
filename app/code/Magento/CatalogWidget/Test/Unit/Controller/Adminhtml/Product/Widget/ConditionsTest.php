<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogWidget\Test\Unit\Controller\Adminhtml\Product\Widget;

use Magento\CatalogWidget\Controller\Adminhtml\Product\Widget\Conditions;
use Magento\CatalogWidget\Model\Rule;
use Magento\CatalogWidget\Model\Rule\Condition\Product;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConditionsTest extends TestCase
{
    /**
     * @var Conditions
     */
    protected $controller;

    /**
     * @var Rule|MockObject
     */
    protected $rule;

    /**
     * @var RequestInterface|MockObject
     */
    protected $request;

    /**
     * @var ResponseInterface|MockObject
     */
    protected $response;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->rule = $this->createMock(Rule::class);
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['setBody', 'sendResponse'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->response->expects($this->once())->method('setBody')->will($this->returnSelf());

        $objectManagerHelper = new ObjectManagerHelper($this);
        $arguments = $objectManagerHelper->getConstructArguments(
            Conditions::class,
            [
                'rule' => $this->rule,
                'response' => $this->response
            ]
        );
        $this->request = $arguments['context']->getRequest();

        $this->objectManager = $arguments['context']->getObjectManager();
        $this->controller = $objectManagerHelper->getObject(
            Conditions::class,
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

        $condition = $this->getMockBuilder(Product::class)
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
            ->with(Product::class)
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
