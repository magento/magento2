<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model\Config\Structure\Element;

use Magento\Paypal\Model\Config\Structure\Element\FieldPlugin as FieldConfigStructurePlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Config\Model\Config\Structure\Element\Field as FieldConfigStructureMock;

class FieldPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FieldConfigStructurePlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var FieldConfigStructureMock|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->subjectMock = $this->getMockBuilder(FieldConfigStructureMock::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(
            FieldConfigStructurePlugin::class,
            ['request' => $this->requestMock]
        );
    }

    public function testAroundGetConfigPathHasResult()
    {
        $someResult = 'some result';

        $this->assertEquals($someResult, $this->plugin->afterGetConfigPath($this->subjectMock, $someResult));
    }

    public function testAroundGetConfigPathNonPaymentSection()
    {
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('section')
            ->willReturn('non-payment');

        $this->assertNull($this->plugin->afterGetConfigPath($this->subjectMock, null));
    }

    /**
     * @param string $subjectPath
     * @param string $expectedConfigPath
     *
     * @dataProvider afterGetConfigPathDataProvider
     */
    public function testAroundGetConfigPath($subjectPath, $expectedConfigPath)
    {
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with('section')
            ->willReturn('payment');
        $this->subjectMock->expects(static::once())
            ->method('getPath')
            ->willReturn($subjectPath);

        $this->assertEquals($expectedConfigPath, $this->plugin->afterGetConfigPath($this->subjectMock, null));
    }

    /**
     * @return array
     */
    public function afterGetConfigPathDataProvider()
    {
        return [
            ['payment_us/group/field', 'payment/group/field'],
            ['payment_other/group/field', 'payment/group/field'],
            ['payment_us', 'payment_us'],
            ['payment_wrong_country/group/field', 'payment_wrong_country/group/field']
        ];
    }
}
