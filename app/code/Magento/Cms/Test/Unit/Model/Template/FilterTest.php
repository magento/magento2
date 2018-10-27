<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model\Template;

/**
 * Work with catalog(store, website) urls
 *
 * @covers \Magento\Cms\Model\Template\Filter
 */
class FilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Cms\Model\Template\Filter
     */
    protected $filter;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->filter = $objectManager->getObject(
            \Magento\Cms\Model\Template\Filter::class,
            ['storeManager' => $this->storeManagerMock]
        );
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
    }

    /**
     * @covers \Magento\Cms\Model\Template\Filter::mediaDirective
     */
    public function testMediaDirective()
    {
        $baseMediaDir = 'pub/media';
        $construction = [
            '{{media url="wysiwyg/image.jpg"}}',
            'media',
            ' url="wysiwyg/image.jpg"'
        ];
        $expectedResult = 'pub/media/wysiwyg/image.jpg';
        $this->storeMock->expects($this->once())
            ->method('getBaseMediaDir')
            ->willReturn($baseMediaDir);
        $this->assertEquals($expectedResult, $this->filter->mediaDirective($construction));
    }
}
