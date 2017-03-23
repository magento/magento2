<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Field\Enable;

/**
 * Class AbstractEnableTest
 *
 * Test for class \Magento\Paypal\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable
 */
class AbstractEnableTest extends \PHPUnit_Framework_TestCase
{
    const EXPECTED_ATTRIBUTE = 'data-enable="stub"';

    /**
     * @var \Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable\Stub
     */
    protected $abstractEnable;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $elementMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->elementMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\AbstractElement::class)
            ->setMethods(
                [
                    'getHtmlId',
                    'getTooltip',
                    'getForm',
                ]
            )->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->abstractEnable = $objectManager->getObject(
            \Magento\Paypal\Test\Unit\Block\Adminhtml\System\Config\Field\Enable\AbstractEnable\Stub::class
        );
    }

    /**
     * Run test for getUiId method
     *
     * @return void
     */
    public function testGetUiId()
    {
        $this->assertContains(self::EXPECTED_ATTRIBUTE, $this->abstractEnable->getUiId());
    }

    /**
     * Run test for render method
     *
     * @return void
     */
    public function testRender()
    {
        $formMock = $this->getMockBuilder(\Magento\Framework\Data\Form::class)
            ->setMethods(['getFieldNameSuffix'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->elementMock->expects($this->any())
            ->method('getHtmlId')
            ->willReturn('test-html-id');
        $this->elementMock->expects($this->once())
            ->method('getTooltip')
            ->willReturn('');
        $this->elementMock->expects($this->any())
            ->method('getForm')
            ->willReturn($formMock);

        $formMock->expects($this->any())
            ->method('getFieldNameSuffix')
            ->willReturn('');

        $this->assertContains(self::EXPECTED_ATTRIBUTE, $this->abstractEnable->render($this->elementMock));
    }
}
