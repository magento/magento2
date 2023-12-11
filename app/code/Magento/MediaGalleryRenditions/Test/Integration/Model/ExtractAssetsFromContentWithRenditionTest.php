<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaGalleryRenditions\Test\Integration\Model;

use Magento\MediaContentApi\Api\ExtractAssetsFromContentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for Extracting assets from rendition paths/urls in content
 */
class ExtractAssetsFromContentWithRenditionTest extends TestCase
{
    /**
     * @var ExtractAssetsFromContentInterface
     */
    private $extractAssetsFromContent;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->extractAssetsFromContent = Bootstrap::getObjectManager()
            ->get(ExtractAssetsFromContentInterface::class);
    }

    /**
     * Assert rendition urls/path in the content are associated with an asset
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     *
     * @dataProvider contentProvider
     * @param string $content
     * @param array $assetIds
     */
    public function testExecute(string $content, array $assetIds): void
    {
        $assets = $this->extractAssetsFromContent->execute($content);

        $extractedAssetIds = [];
        foreach ($assets as $asset) {
            $extractedAssetIds[] = $asset->getId();
        }

        sort($assetIds);
        sort($extractedAssetIds);

        $this->assertEquals($assetIds, $extractedAssetIds);
    }

    /**
     * Data provider for testExecute
     *
     * @return array
     */
    public function contentProvider()
    {
        return [
            'Empty Content' => [
                '',
                []
            ],
            'No paths in content' => [
                'content without paths',
                []
            ],
            'Relevant rendition path in content' => [
                'content {{media url=".renditions/testDirectory/path.jpg"}} content',
                [
                    2020
                ]
            ],
            'Relevant wysiwyg rendition path in content' => [
                'content <img src="https://domain.com/media/.renditions/testDirectory/path.jpg"}} content',
                [
                    2020
                ]
            ],
            'Relevant rendition path content with pub' => [
                '/pub/media/.renditions/testDirectory/path.jpg',
                [
                    2020
                ]
            ],
            'Relevant rendition path content' => [
                '/media/.renditions/testDirectory/path.jpg',
                [
                    2020
                ]
            ],
            'Relevant existing media paths w/o rendition in content' => [
                'content {{media url="testDirectory/path.jpg"}} content',
                [
                    2020
                ]
            ],
            'Relevant existing paths w/o rendition in content with pub' => [
                '/pub/media/testDirectory/path.jpg',
                [
                    2020
                ]
            ],
            'Non-existing rendition paths in content' => [
                'content {{media url=".renditions/non-existing-path.png"}} content',
                []
            ]
        ];
    }
}
