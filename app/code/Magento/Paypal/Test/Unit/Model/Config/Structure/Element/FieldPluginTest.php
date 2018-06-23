<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Model\Config\Structure\Element;

class FieldPluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var FieldPlugin */
    protected $model;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var  \Magento\Config\Model\Config\Structure\Element\Field|\PHPUnit_Framework_MockObject_MockObject */
    protected $subject;

    protected function setUp()
    {
        $this->request = $this->getMockForAbstractClass('Magento\Framework\App\RequestInterface');
        $this->subject = $this->getMock('Magento\Config\Model\Config\Structure\Element\Field', [], [], '', false);

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject(
            'Magento\Paypal\Model\Config\Structure\Element\FieldPlugin',
            ['request' => $this->request]
        );
    }

    public function testAroundGetConfigPathHasResult()
    {
        $someResult = 'some result';
        $callback = function () use ($someResult) {
            return $someResult;
        };
        $this->assertEquals($someResult, $this->model->aroundGetConfigPath($this->subject, $callback));
    }

    public function testAroundGetConfigPathNonPaymentSection()
    {
        $callback = function () {
            return null;
        };
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('section')
            ->will($this->returnValue('non-payment'));
        $this->assertNull($this->model->aroundGetConfigPath($this->subject, $callback));
    }

    /**
     * @param string $subjectPath
     * @param string $expectedConfigPath
     * @dataProvider aroundGetConfigPathDataProvider
     */
    public function testAroundGetConfigPath($subjectPath, $expectedConfigPath)
    {
        $callback = function () {
            return null;
        };
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('section')
            ->will($this->returnValue('payment'));
        $this->subject->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue($subjectPath));
        $this->assertEquals($expectedConfigPath, $this->model->aroundGetConfigPath($this->subject, $callback));
    }

    /**
     * @return array
     */
    public function aroundGetConfigPathDataProvider()
    {
        return [
            ['payment_us/group/field', 'payment/group/field'],
            ['payment_other/group/field', 'payment/group/field'],
            ['payment_us', 'payment_us'],
            ['payment_wrong_country/group/field', 'payment_wrong_country/group/field'],
        ];
    }
}
