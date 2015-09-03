<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Block\Adminhtml\Product\Edit;

class NewVideoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /*
     * @var \Magento\Framework\UrlInterface||\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formFactoryMock;

    /**
     * @var \Magento\Framework\Json\EncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonEncoderMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     * |\Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo
     */
    protected $block;

    public function setUp()
    {
        $this->contextMock = $this->getMock('\Magento\Backend\Block\Template\Context', [], [], '', false);
        $this->mathRandom = $this->getMock('\Magento\Framework\Math\Random', [], [], '', false);
        $this->urlBuilder = $this->getMock('\Magento\Framework\UrlInterface', [], [], '', false);
        $this->contextMock->expects($this->any())->method('getMathRandom')->willReturn($this->mathRandom);
        $this->contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilder);
        $this->registryMock = $this->getMock('\Magento\Framework\Registry', [], [], '', false);
        $this->formFactoryMock = $this->getMock('\Magento\Framework\Data\FormFactory', [], [], '', false);
        $this->jsonEncoderMock = $this->getMock('\Magento\Framework\Json\EncoderInterface', [], [], '', false);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->block = $objectManager->getObject(
            '\Magento\ProductVideo\Block\Adminhtml\Product\Edit\NewVideo',
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'formFactory' => $this->formFactoryMock,
                'jsonEncoder' => $this->jsonEncoderMock
            ]
        );
    }

    public function testGetHtmlId()
    {
        $this->mathRandom->expects($this->any())->method('getUniqueHash')->with('id_')->willReturn('id_' . rand());
        $this->block->getHtmlId();
    }

    public function testGetAfterElementHtml()
    {
        $rand = rand();
        $this->mathRandom->expects($this->any())->method('getUniqueHash')->with('id_')->willReturn('id_' . $rand);
        $url = 'http://host/index.php/admin/catalog/product_gallery/upload/key/';
        $this->urlBuilder->expects($this->once())->method('getUrl')->willReturn($url);
        $value = [
            'saveVideoUrl' => $url,
            'htmlId' => 'id_' . $rand
        ];
        $this->jsonEncoderMock->expects($this->once())->method('encode')->with(
            $value
        )->willReturn(
            json_encode($value)
        );
        $this->block->getAfterElementHtml();
    }
}
