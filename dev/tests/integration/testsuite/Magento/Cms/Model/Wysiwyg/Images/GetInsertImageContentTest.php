<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg\Images;

use Magento\Backend\Model\UrlInterface;
use Magento\Cms\Helper\Wysiwyg\Images as ImagesHelper;
use Magento\Framework\Url\EncoderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetInsertImageContentTest extends TestCase
{
    /**
     * @var GetInsertImageContent
     */
    private $getInsertImageContent;

    /**
     * @var ImagesHelper
     */
    private $imagesHelper;

    /**
     * @var EncoderInterface
     */
    private $urlEncoder;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getInsertImageContent = Bootstrap::getObjectManager()->get(GetInsertImageContent::class);
        $this->imagesHelper = Bootstrap::getObjectManager()->get(ImagesHelper::class);
        $this->urlEncoder = Bootstrap::getObjectManager()->get(EncoderInterface::class);
        $this->url = Bootstrap::getObjectManager()->get(UrlInterface::class);
    }

    /**
     * Test for GetInsertImageContent::execute
     *
     * @dataProvider imageDataProvider
     * @param string $filename
     * @param bool $forceStaticPath
     * @param bool $renderAsTag
     * @param int|null $storeId
     * @param string $expectedResult
     */
    public function testExecute(
        string $filename,
        bool $forceStaticPath,
        bool $renderAsTag,
        ?int $storeId,
        string $expectedResult
    ): void {
        if (!$forceStaticPath && !$renderAsTag && !$this->imagesHelper->isUsingStaticUrlsAllowed()) {
            $expectedResult = $this->url->getUrl(
                'cms/wysiwyg/directive',
                [
                    '___directive' => $this->urlEncoder->encode($expectedResult),
                    '_escape_params' => false
                ]
            );
        }

        $this->assertEquals(
            $expectedResult,
            $this->getInsertImageContent->execute(
                $this->imagesHelper->idEncode($filename),
                $forceStaticPath,
                $renderAsTag,
                $storeId
            )
        );
    }

    /**
     * Data provider for testExecute
     *
     * @return array[]
     */
    public function imageDataProvider(): array
    {
        return [
            [
                'test-image.jpg',
                false,
                true,
                1,
                '<img src="{{media url=&quot;test-image.jpg&quot;}}" alt="" />'
            ],
            [
                'catalog/category/test-image.jpg',
                true,
                false,
                1,
                '/pub/media/catalog/category/test-image.jpg'
            ],
            [
                'test-image.jpg',
                false,
                false,
                1,
                '{{media url="test-image.jpg"}}'
            ],
            [
                '/test-image.jpg',
                false,
                true,
                2,
                '<img src="{{media url=&quot;/test-image.jpg&quot;}}" alt="" />'
            ],
            [
                'test-image.jpg',
                false,
                true,
                null,
                '<img src="{{media url=&quot;test-image.jpg&quot;}}" alt="" />'
            ],
        ];
    }
}
