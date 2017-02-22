<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductVideo\Test\Unit\Model\Plugin;

/**
 * Class BaseImageTest
 */
class BaseImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $templateMock;

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\BaseImage
     */
    protected $baseImageMock;

    /**
     * @var \Magento\ProductVideo\Model\Plugin\BaseImage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pluginObject;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->templateMock = $this->getMock('\Magento\Framework\View\Element\Template', ['assign'], [], '', false);
        $this->baseImageMock =
            $this->getMock('\Magento\Catalog\Block\Adminhtml\Product\Helper\Form\BaseImage', [], [], '', false);
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->pluginObject = $objectManager->getObject(
            '\Magento\ProductVideo\Model\Plugin\BaseImage',
            [

            ]
        );
    }

    /**
     * Test afterAssignBlockVariables()
     */
    public function testAfterAssignBlockVariables()
    {
        $this->templateMock->expects($this->once())->method('assign')->willReturn($this->templateMock);
        $this->pluginObject->afterAssignBlockVariables($this->baseImageMock, $this->templateMock);
    }

    /**
     * Test afterCreateElementHtmlOutputBlock()
     */
    public function testAfterCreateElementHtmlOutputBlock()
    {
        $this->templateMock->expects($this->any())->method('setTemplate')->willReturn(
            'Magento_ProductVideo::product/edit/base_image.phtml'
        );
        $this->pluginObject->afterCreateElementHtmlOutputBlock($this->baseImageMock, $this->templateMock);
    }
}
