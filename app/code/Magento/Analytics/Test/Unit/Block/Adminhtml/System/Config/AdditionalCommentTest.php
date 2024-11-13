<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Block\Adminhtml\System\Config;

use Magento\Analytics\Block\Adminhtml\System\Config\AdditionalComment;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdditionalCommentTest extends TestCase
{
    /**
     * @var AdditionalComment
     */
    private $additionalComment;

    /**
     * @var AbstractElement|MockObject
     */
    private $abstractElementMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Form|MockObject
     */
    private $formMock;

    protected function setUp(): void
    {
        $this->abstractElementMock = $this->getMockBuilder(AbstractElement::class)
            ->addMethods(['getComment', 'getLabel'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $escaper = $objectManager->getObject(Escaper::class);
        $reflection = new \ReflectionClass($this->abstractElementMock);
        $reflection_property = $reflection->getProperty('_escaper');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($this->abstractElementMock, $escaper);

        $this->abstractElementMock->setEscaper($escaper);
        $this->contextMock = $this->createMock(Context::class);
        $this->formMock = $this->createMock(Form::class);

        $objectManager = new ObjectManager($this);
        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->createMock(SecureHtmlRenderer::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
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
        $this->abstractElementMock
            ->method('getComment')
            ->willReturn('New comment');
        $this->abstractElementMock
            ->method('getLabel')
            ->willReturn('Comment label');
        $html = $this->additionalComment->render($this->abstractElementMock);
        $this->assertMatchesRegularExpression(
            "/New comment/",
            $html
        );
        $this->assertMatchesRegularExpression(
            "/Comment label/",
            $html
        );
    }
}
