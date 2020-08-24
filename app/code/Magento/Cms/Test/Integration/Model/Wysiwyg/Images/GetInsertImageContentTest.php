<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Test\Integration\Model\Wysiwyg\Images;

use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Cms\Helper\Wysiwyg\Images as ImagesHelper;
use Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent;
use Magento\Framework\Url\EncoderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetInsertImageContentTest extends TestCase
{
    private const TEST_IMAGE_FILE = '/test-image.jpg';

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
     * @var BackendHelper
     */
    protected $_backendData;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getInsertImageContent = Bootstrap::getObjectManager()->get(GetInsertImageContent::class);
        $this->imagesHelper = Bootstrap::getObjectManager()->get(ImagesHelper::class);
        $this->urlEncoder = Bootstrap::getObjectManager()->get(EncoderInterface::class);
        $this->_backendData = Bootstrap::getObjectManager()->get(BackendHelper::class);
    }

    /**
     * Test for GetInsertImageContent::execute
     *
     * @dataProvider imageDataProvider
     * @param string $encodedFilename
     * @param bool $forceStaticPath
     * @param bool $renderAsTag
     * @param int|null $storeId
     * @param string|null $expectedResult
     */
    public function testExecute(
        string $encodedFilename,
        bool $forceStaticPath,
        bool $renderAsTag,
        ?int $storeId = null,
        ?string $expectedResult = null
    ): void {
        $getImageForInsert = $this->getInsertImageContent->execute(
            $encodedFilename,
            $forceStaticPath,
            $renderAsTag,
            $storeId
        );

        if (!$forceStaticPath && !$renderAsTag) {
            if (!$this->imagesHelper->isUsingStaticUrlsAllowed()) {

                $encodedDirective = $this->urlEncoder->encode($expectedResult);
                $expectedResult = $this->_backendData->getUrl(
                    'cms/wysiwyg/directive',
                    [
                        '___directive' => $encodedDirective,
                        '_escape_params' => false,
                    ]
                );
            }
        }

        $this->assertEquals($getImageForInsert, $expectedResult);
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
                'L3Rlc3QtaW1hZ2UuanBn',
                false,
                true,
                1,
                '<img src="{{media url=&quot;' . self::TEST_IMAGE_FILE . '&quot;}}" alt="" />'
            ],
            [
                'L3Rlc3QtaW1hZ2UuanBn',
                true,
                false,
                1,
                '/pub/media/' . self::TEST_IMAGE_FILE
            ],
            [
                'L3Rlc3QtaW1hZ2UuanBn',
                false,
                false,
                1,
                '{{media url="' . self::TEST_IMAGE_FILE . '"}}'
            ],
            [
                'L3Rlc3QtaW1hZ2UuanBn',
                false,
                true,
                2,
                '<img src="{{media url=&quot;' . self::TEST_IMAGE_FILE . '&quot;}}" alt="" />'
            ],
            [
                'L3Rlc3QtaW1hZ2UuanBn',
                false,
                true,
                null,
                '<img src="{{media url=&quot;' . self::TEST_IMAGE_FILE . '&quot;}}" alt="" />'
            ],
        ];
    }
}
