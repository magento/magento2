<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\MediaContentApi\Api\ExtractAssetsFromContentInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for ExtractAssetsFromContent
 */
class ExtractAssetsFromContentTest extends TestCase
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
     * Assing assets to content, retrieve the data, then unassign assets from content
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
            'Relevant paths in content' => [
                'content {{media url="testDirectory/path.jpg"}} content',
                [
                    2020
                ]
            ],
            'Relevant wysiwyg paths in content' => [
                'content <img src="https://domain.com/media/testDirectory/path.jpg"}} content',
                [
                    2020
                ]
            ],
            'Relevant path content with pub' => [
                '/pub/media/testDirectory/path.jpg',
                [
                    2020
                ]
            ],
            'Relevant path content' => [
                '/media/testDirectory/path.jpg',
                [
                    2020
                ]
            ],
            'Irrelevant paths in content' => [
                'content {{media url="media/non-existing-path.png"}} content',
                []
            ],
        ];
    }
}
