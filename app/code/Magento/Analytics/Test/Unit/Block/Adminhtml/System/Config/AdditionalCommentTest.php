<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Block\Adminhtml\System\Config;

use Magento\Analytics\Block\Adminhtml\System\Config\AdditionalComment;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AdditionalCommentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AdditionalComment
     */
    private $additionalComment;

    /**
     * @var AbstractElement|\PHPUnit_Framework_MockObject_MockObject
     */
    private $abstractElementMock;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var Form|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formMock;

    protected function setUp()
    {
        $this->abstractElementMock = $this->getMockBuilder(AbstractElement::class)
            ->setMethods(['getComment', 'getLabel'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $escaper = $objectManager->getObject(\Magento\Framework\Escaper::class);
        $reflection = new \ReflectionClass($this->abstractElementMock);
        $reflection_property = $reflection->getProperty('_escaper');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->abstractElementMock, $escaper);

        $this->abstractElementMock->setEscaper($escaper);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formMock = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->additionalComment = $objectManager->getObject(
            AdditionalComment::class,
            [
                'context' => $this->contextMock
            ]
        );
    }

    public function testRender()
    {
        $this->abstractElementMock->setForm($this->formMock);
        $this->abstractElementMock->expects($this->any())
            ->method('getComment')
            ->willReturn('New comment');
        $this->abstractElementMock->expects($this->any())
            ->method('getLabel')
            ->willReturn('Comment label');
        $html = $this->additionalComment->render($this->abstractElementMock);
        $this->assertRegExp(
            "/New comment/",
            $html
        );
        $this->assertRegExp(
            "/Comment label/",
            $html
        );
    }
}
