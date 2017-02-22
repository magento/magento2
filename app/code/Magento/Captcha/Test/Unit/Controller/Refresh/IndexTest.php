<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

    protected function setUp()
    {
        $this->captchaHelperMock = $this->getMock('Magento\Captcha\Helper\Data', [], [], '', false);
        $this->captchaMock = $this->getMock('Magento\Captcha\Model\DefaultModel', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->contextMock = $this->getMock('Magento\Framework\App\Action\Context', [], [], '', false);
        $this->viewMock = $this->getMock('Magento\Framework\App\ViewInterface');
        $this->layoutMock = $this->getMock('Magento\Framework\View\LayoutInterface');
        $this->flagMock = $this->getMock('Magento\Framework\App\ActionFlag', [], [], '', false);

        $this->contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $this->contextMock->expects($this->any())->method('getView')->will($this->returnValue($this->viewMock));
        $this->contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $this->contextMock->expects($this->any())->method('getActionFlag')->will($this->returnValue($this->flagMock));
        $this->viewMock->expects($this->any())->method('getLayout')->will($this->returnValue($this->layoutMock));

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
        $blockMock = $this->getMock('Magento\Captcha\Block\Captcha', $blockMethods, [], '', false);

        $this->requestMock->expects($this->any())->method('getPost')->with('formId')->will($this->returnValue($formId));
        $this->requestMock->expects($this->exactly($callsNumber))->method('getContent')
            ->will($this->returnValue(json_encode($content)));
        $this->captchaHelperMock->expects($this->any())->method('getCaptcha')->with($formId)
            ->will($this->returnValue($this->captchaMock));
        $this->captchaMock->expects($this->once())->method('generate');
        $this->captchaMock->expects($this->once())->method('getBlockName')->will($this->returnValue('block'));
        $this->captchaMock->expects($this->once())->method('getImgSrc')->will($this->returnValue('source'));
        $this->layoutMock->expects($this->once())->method('createBlock')->with('block')
            ->will($this->returnValue($blockMock));
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
