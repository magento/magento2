<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Template;

use Magento\Cms\Model\Template\Filter;
use Magento\Framework\Filter\Template\FilteringDepthMeter;
use Magento\Framework\Filter\Template\SignatureProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Work with catalog(store, website) urls
 *
 * @covers \Magento\Cms\Model\Template\Filter
 */
class FilterTest extends TestCase
{
    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var Filter
     */
    protected $filter;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                SignatureProvider::class,
                $this->createMock(SignatureProvider::class)
            ],
            [
                FilteringDepthMeter::class,
                $this->createMock(FilteringDepthMeter::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);

        $this->filter = $objectManager->getObject(
            Filter::class,
            ['storeManager' => $this->storeManagerMock]
        );
        $this->storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($this->storeMock);
    }

    /**
     * Test processing media directives.
     *
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

    /**
     * Test the directive when HTML quotes used.
     *
     * @covers \Magento\Cms\Model\Template\Filter::mediaDirective
     */
    public function testMediaDirectiveWithEncodedQuotes()
    {
        $baseMediaDir = 'pub/media';
        $construction = [
            '{{media url=&quot;wysiwyg/image.jpg&quot;}}',
            'media',
            ' url=&quot;wysiwyg/image.jpg&quot;'
        ];
        $expectedResult = 'pub/media/wysiwyg/image.jpg';

        $this->storeMock->expects($this->once())
            ->method('getBaseMediaDir')
            ->willReturn($baseMediaDir);
        $this->assertEquals($expectedResult, $this->filter->mediaDirective($construction));
    }

    /**
     * Test using media directive with relative path to image.
     *
     * @covers \Magento\Cms\Model\Template\Filter::mediaDirective
     */
    public function testMediaDirectiveRelativePath()
    {
        $this->expectException('InvalidArgumentException');
        $baseMediaDir = 'pub/media';
        $construction = [
            '{{media url="wysiwyg/images/../image.jpg"}}',
            'media',
            ' url="wysiwyg/images/../image.jpg"'
        ];
        $this->storeMock->expects($this->any())
            ->method('getBaseMediaDir')
            ->willReturn($baseMediaDir);
        $this->filter->mediaDirective($construction);
    }

    /**
     * Test using media directive with a URL path including schema.
     *
     * @covers \Magento\Cms\Model\Template\Filter::mediaDirective
     */
    public function testMediaDirectiveURL()
    {
        $this->expectException(\InvalidArgumentException::class);

        $baseMediaDir = 'pub/media';
        $construction = [
            '{{media url="http://wysiwyg/images/image.jpg"}}',
            'media',
            ' url="http://wysiwyg/images/../image.jpg"'
        ];
        $this->storeMock->expects($this->any())
            ->method('getBaseMediaDir')
            ->willReturn($baseMediaDir);
        $this->filter->mediaDirective($construction);
    }
}
