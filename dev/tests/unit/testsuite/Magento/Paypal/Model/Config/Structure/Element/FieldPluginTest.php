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

namespace Magento\Paypal\Model\Config\Structure\Element;

class FieldPluginTest extends \PHPUnit_Framework_TestCase
{
    /** @var FieldPlugin */
    protected $model;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var  \Magento\Backend\Model\Config\Structure\Element\Field|\PHPUnit_Framework_MockObject_MockObject */
    protected $subject;

    protected function setUp()
    {
        $this->request = $this->getMockForAbstractClass('Magento\Framework\App\RequestInterface');
        $this->subject = $this->getMock('Magento\Backend\Model\Config\Structure\Element\Field', [], [], '', false);

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
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
