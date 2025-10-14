<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form\Element\DataType\Media;

use Magento\Framework\File\Size;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\DataType\Media\Image;
use Magento\Ui\Test\Unit\Component\Form\Element\DataType\MediaTest;
use PHPUnit\Framework\MockObject\MockObject;

class ImageTest extends MediaTest
{
    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Size|MockObject
     */
    private $fileSize;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Image
     */
    private $image;

    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->atLeastOnce())->method('getProcessor')->willReturn($this->processor);

        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $this->store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->store->expects($this->any())->method('getId')->willReturn(0);

        $this->storeManager->expects($this->any())->method('getStore')->willReturn($this->store);

        $this->fileSize = $this->getMockBuilder(Size::class)
            ->getMock();

        $this->objectManager = new ObjectManager($this);

        $this->image = $this->objectManager->getObject(Image::class, [
            'context' => $this->context,
            'storeManager' => $this->storeManager,
            'fileSize' => $this->fileSize
        ]);

        $this->image->setData([
            'config' => [
                'initialMediaGalleryOpenSubpath' => 'open/sesame',
            ],
        ]);
    }

    /**
     * @dataProvider prepareDataProvider
     */
    public function testPrepare()
    {
        $this->assertExpectedPreparedConfiguration(...func_get_args());
    }

    /**
     * Data provider for testPrepare
     * @return array
     */
    public static function prepareDataProvider(): array
    {
        return [
            [['maxFileSize' => 10], 10, ['maxFileSize' => 10]],
            [['maxFileSize' => null], 10, ['maxFileSize' => 10]],
            [['maxFileSize' => 10], 5, ['maxFileSize' => 5]],
            [['maxFileSize' => 10], 20, ['maxFileSize' => 10]],
            [['maxFileSize' => 0], 10, ['maxFileSize' => 10]],
        ];
    }

    /**
     * @param array $initialConfig
     * @param int $maxFileSizeSupported
     * @param array $expectedPreparedConfig
     */
    private function assertExpectedPreparedConfiguration(
        array $initialConfig,
        int $maxFileSizeSupported,
        array $expectedPreparedConfig
    ) {
        $this->image->setData(array_merge_recursive(['config' => $initialConfig], $this->image->getData()));

        $this->fileSize->expects($this->any())->method('getMaxFileSize')->willReturn($maxFileSizeSupported);

        $this->image->prepare();

        $actualRelevantPreparedConfig = array_intersect_key($this->image->getConfiguration(), $initialConfig);

        $this->assertEquals(
            $expectedPreparedConfig,
            $actualRelevantPreparedConfig
        );
    }
}
