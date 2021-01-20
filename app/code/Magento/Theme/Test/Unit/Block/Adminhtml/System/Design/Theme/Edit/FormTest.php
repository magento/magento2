<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Block\Adminhtml\System\Design\Theme\Edit;

class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    protected function setUp(): void
    {
        $this->_objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testToHtml()
    {
        /** @var \Magento\Framework\Data\FormFactory|\PHPUnit\Framework\MockObject\MockObject $customerHelper */
        $formFactoryMock = $this->getMockBuilder(\Magento\Framework\Data\FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Framework\Data\Form|\PHPUnit\Framework\MockObject\MockObject $customerHelper */
        $formMock = $this->getMockBuilder(\Magento\Framework\Data\Form::class)
            ->setMethods(['setUseContainer', 'setParent', 'setBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Framework\UrlInterface|\PHPUnit\Framework\MockObject\MockObject $customerHelper */
        $urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)
            ->getMockForAbstractClass();

        /** @var \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form $block */
        $block = $this->_objectManagerHelper->getObject(
            \Magento\Theme\Block\Adminhtml\System\Design\Theme\Edit\Form::class,
            [
                'formFactory' => $formFactoryMock,
                'urlBuilder' => $urlBuilderMock,
            ]
        );
        $block->setTemplate('');

        $urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('adminhtml/*/save', [])
            ->willReturn('save_url');
        $urlBuilderMock->expects($this->once())
            ->method('getBaseUrl')
            ->with([])
            ->willReturn('base_url');

        $formFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                [
                    'data' => [
                        'id' => 'edit_form',
                        'action' => 'save_url',
                        'enctype' => 'multipart/form-data',
                        'method' => 'post',
                    ],
                ]
            )->willReturn($formMock);

        $formMock->expects($this->once())
            ->method('setUseContainer')
            ->with(true);
        $formMock->expects($this->once())
            ->method('setParent')
            ->with($block);
        $formMock->expects($this->once())
            ->method('setBaseUrl')
            ->with('base_url');

        $this->assertEquals('', $block->toHtml());
    }
}
