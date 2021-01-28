<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Controller\Refresh;

class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $captchaHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $captchaMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $viewMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $layoutMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $flagMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $serializerMock;

    /**
     * @var \Magento\Captcha\Controller\Refresh\Index
     */
    protected $model;

    protected function setUp(): void
    {
        $this->captchaHelperMock = $this->createMock(\Magento\Captcha\Helper\Data::class);
        $this->captchaMock = $this->createMock(\Magento\Captcha\Model\DefaultModel::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->contextMock = $this->createMock(\Magento\Framework\App\Action\Context::class);
        $this->viewMock = $this->createMock(\Magento\Framework\App\ViewInterface::class);
        $this->layoutMock = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->flagMock = $this->createMock(\Magento\Framework\App\ActionFlag::class);
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\Serializer\Json::class);

        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())->method('getActionFlag')->willReturn($this->flagMock);
        $this->viewMock->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);

        $this->model = new \Magento\Captcha\Controller\Refresh\Index(
            $this->contextMock,
            $this->captchaHelperMock,
            $this->serializerMock
        );
    }

    /**
     * @dataProvider executeDataProvider
     * @param int $formId
     * @param int $callsNumber
     */
    public function testExecute($formId, $callsNumber)
    {
        $content = ['formId' => $formId];
        $imgSource = ['imgSrc' => 'source'];

        $blockMethods = ['setFormId', 'setIsAjax', 'toHtml'];
        $blockMock = $this->createPartialMock(\Magento\Captcha\Block\Captcha::class, $blockMethods);

        $this->requestMock->expects($this->any())->method('getPost')->with('formId')->willReturn($formId);
        $this->requestMock->expects($this->exactly($callsNumber))->method('getContent')
            ->willReturn(json_encode($content));
        $this->captchaHelperMock->expects($this->any())->method('getCaptcha')->with($formId)
            ->willReturn($this->captchaMock);
        $this->captchaMock->expects($this->once())->method('generate');
        $this->captchaMock->expects($this->once())->method('getBlockName')->willReturn('block');
        $this->captchaMock->expects($this->once())->method('getImgSrc')->willReturn('source');
        $this->layoutMock->expects($this->once())->method('createBlock')->with('block')
            ->willReturn($blockMock);
        $blockMock->expects($this->any())->method('setFormId')->with($formId)->willReturn($blockMock);
        $blockMock->expects($this->any())->method('setIsAjax')->with(true)->willReturn($blockMock);
        $blockMock->expects($this->once())->method('toHtml');
        $this->responseMock->expects($this->once())->method('representJson')->with(json_encode($imgSource));
        $this->flagMock->expects($this->once())->method('set')->with('', 'no-postDispatch', true);
        $this->serializerMock->expects($this->exactly($callsNumber))
            ->method('unserialize')->willReturn($content);
        $this->serializerMock->expects($this->once())
            ->method('serialize')->willReturn(json_encode($imgSource));

        $this->model->execute();
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                'formId' => null,
                'callsNumber' => 1,
            ],
            [
                'formId' => 1,
                'callsNumber' => 0,
            ]
        ];
    }
}
