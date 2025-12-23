<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Fixture;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Filesystem;
use Magento\TestFramework\Fixture\Data\ProcessorInterface;

/**
 * Creates an image fixture
 *
 * Example: Basic usage. This will create a jpeg image in var/tmp/fixtures/ directory with unique name
 *  ```php
 *     #[
 *         DataFixture(ImageFixture::class, as: 'image')
 *     ],
 *  ```
 * Example: Create an image and save it in a custom directory in var/tmp directory
 *
 * ```php
 *    #[
 *        DataFixture(ImageFixture::class, ['path' => 'custom/dir/image.jpeg'])
 *    ],
 * ```
 * Example: Create an image with custom sizes
 *
 * ```php
 *    #[
 *        DataFixture(ImageFixture::class, ['width' => 200, 'height' => 100])
 *    ],
 * ```
 */
class ImageFixture implements RevertibleDataFixtureInterface
{
    private const array DEFAULT_DATA = [
        'directory' => DirectoryList::TMP,
        'type' => 'image/jpeg',
        'path' => 'fixtures/%uniqid%.%ext%',
        'content' => '',
        'width' => 1024,
        'height' => 768,
    ];

    /**
     * @param Filesystem $filesystem
     * @param ProcessorInterface $dataProcessor
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly ProcessorInterface $dataProcessor,
        private readonly DataObjectFactory $dataObjectFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = $this->dataProcessor->process($this, array_merge(self::DEFAULT_DATA, $data));
        $directory = $this->filesystem->getDirectoryWrite($data['directory']);
        $ext = 'jpeg';
        if (!$data['content']) {
            ob_start();
            $image = imagecreatetruecolor($data['width'], $data['height']);
            switch ($data['type']) {
                case 'image/jpeg':
                    imagejpeg($image);
                    break;
                case 'image/png':
                    imagepng($image);
                    $ext = 'png';
                    break;
                case 'image/gif':
                    imagegif($image);
                    $ext = 'gif';
                    break;
                default:
                    throw new \InvalidArgumentException('Unsupported image type');
            }
            $data['content'] = ob_get_clean();
            imagedestroy($image);
        }
        $data['path'] = str_replace('%ext%', $ext, $data['path']);
        $directory->writeFile($data['path'], $data['content']);
        $data['absolute_path'] = $directory->getAbsolutePath($data['path']);

        return $this->dataObjectFactory->create(['data' => $data]);
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $directory = $this->filesystem->getDirectoryWrite($data['directory']);
        $directory->delete($data['path']);
    }
}
