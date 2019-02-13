<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Captcha\Test\Unit\Controller\Refresh;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $captchaHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $captchaMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $flagMock;

    /**
     * @var \Magento\Captcha\Controller\Refresh\Index
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->captchaHelperMock = $this->getMock(\Magento\Captcha\Helper\Data::class, [], [], '', false);
        $this->captchaMock = $this->getMock(\Magento\Captcha\Model\DefaultModel::class, [], [], '', false);
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->responseMock = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->contextMock = $this->getMock(\Magento\Framework\App\Action\Context::class, [], [], '', false);
        $this->viewMock = $this->getMock(\Magento\Framework\App\ViewInterface::class);
        $this->layoutMock = $this->getMock(\Magento\Framework\View\LayoutInterface::class);
        $this->flagMock = $this->getMock(\Magento\Framework\App\ActionFlag::class, [], [], '', false);

        $this->contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);
        $this->contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects($this->any())->method('getActionFlag')->willReturn($this->flagMock);
        $this->viewMock->expects($this->any())->method('getLayout')->willReturn($this->layoutMock);

        $this->model = new \Magento\Captcha\Controller\Refresh\Index($this->contextMock, $this->captchaHelperMock);
    }

    /**
     * @dataProvider executeDataProvider
     * @param int $formId
     * @param int $callsNumber
     */
    public function testExecute($formId, $callsNumber)
    {
        $content = ['formId' => $formId];

        $blockMethods = ['setFormId', 'setIsAjax', 'toHtml'];
        $blockMock = $this->getMock(\Magento\Captcha\Block\Captcha::class, $blockMethods, [], '', false);

        $this->requestMock->expects($this->once())->method('isPost')->willReturn(true);
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
        $blockMock->expects($this->any())->method('setFormId')->with($formId)->will($this->returnValue($blockMock));
        $blockMock->expects($this->any())->method('setIsAjax')->with(true)->will($this->returnValue($blockMock));
        $blockMock->expects($this->once())->method('toHtml');
        $this->responseMock->expects($this->once())->method('representJson')->with(json_encode(['imgSrc' => 'source']));
        $this->flagMock->expects($this->once())->method('set')->with('', 'no-postDispatch', true);

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
