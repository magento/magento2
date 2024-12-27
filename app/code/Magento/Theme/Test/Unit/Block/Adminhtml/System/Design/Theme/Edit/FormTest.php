<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Adminhtml\System\Design\Theme\Edit;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManagerHelper;

    protected function setUp(): void
    {
        $this->_objectManagerHelper = new ObjectManager($this);
    }

    public function testToHtml()
    {
        /** @var FormFactory|MockObject $customerHelper */
        $formFactoryMock = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Form|MockObject $customerHelper */
        $formMock = $this->getMockBuilder(Form::class)
            ->addMethods(['setUseContainer', 'setParent', 'setBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var UrlInterface|MockObject $customerHelper */
        $urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
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
