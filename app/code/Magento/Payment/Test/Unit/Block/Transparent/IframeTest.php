<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Block\Transparent;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class that tests the iframe block
 */
class IframeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Payment\Block\Transparent\Iframe | \PHPUnit_Framework_MockObject_MockObject
     */
    private $iframeBlock;

    /**
     * @var \Magento\Framework\Registry | \PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->iframeBlock = $objectManagerHelper->getObject(
            \Magento\Payment\Block\Transparent\Iframe::class,
            [
                'coreRegistry' => $this->registryMock,
            ]
        );
    }

    public function testToHtml()
    {
        $registryParams = ['registry'];
        $dataParams = [1, 2, 3];

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(\Magento\Payment\Block\Transparent\Iframe::REGISTRY_KEY)
            ->willReturn($registryParams);
        $this->iframeBlock->toHtml();
        $this->assertEquals($registryParams, $this->iframeBlock->getData('params'));
        $this->iframeBlock->setData('params', $dataParams);
        $this->assertSame($dataParams, $this->iframeBlock->getData('params'));
    }
}
