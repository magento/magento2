<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Test\Integration\Model\Wysiwyg\Images;

use Magento\Cms\Helper\Wysiwyg\Images as ImagesHelper;
use Magento\Cms\Model\Wysiwyg\Images\GetInsertImageContent;
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
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->getInsertImageContent = Bootstrap::getObjectManager()->get(GetInsertImageContent::class);
        $this->imagesHelper = Bootstrap::getObjectManager()->get(ImagesHelper::class);
    }

    /**
     * Test for GetInsertImageContent::execute
     *
     * @dataProvider imageDataProvider
     * @param string $encodedFilename
     * @param bool $forceStaticPath
     * @param bool $renderAsTag
     * @param int|null $storeId
     */
    public function testExecute(
        string $encodedFilename,
        bool $forceStaticPath,
        bool $renderAsTag,
        ?int $storeId = null
    ): void {
        $getImageForInsert = $this->getInsertImageContent->execute(
            $encodedFilename,
            $forceStaticPath,
            $renderAsTag,
            $storeId
        );

        if (!$forceStaticPath) {
            if ($renderAsTag) {
                $html = $this->imagesHelper->getImageHtmlDeclaration(
                    self::TEST_IMAGE_FILE,
                    true
                );
                $this->assertEquals(
                    $getImageForInsert,
                    $html
                );
            } else {
                $html = $this->imagesHelper->getImageHtmlDeclaration(
                    self::TEST_IMAGE_FILE,
                    false
                );
                $this->assertEquals(
                    $getImageForInsert,
                    $html
                );
            }
        } else {
            $this->assertEquals(
                $getImageForInsert,
                parse_url(
                    $this->imagesHelper->getCurrentUrl() . self::TEST_IMAGE_FILE,
                    PHP_URL_PATH
                )
            );
        }
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
            ],
            [
                'L3Rlc3QtaW1hZ2UuanBn',
                true,
                false,
                1,
            ],
            [
                'L3Rlc3QtaW1hZ2UuanBn',
                false,
                false,
                1,
            ],
            [
                'L3Rlc3QtaW1hZ2UuanBn',
                false,
                true,
                2,
            ],
            [
                'L3Rlc3QtaW1hZ2UuanBn',
                false,
                true,
                null,
            ],
        ];
    }
}
