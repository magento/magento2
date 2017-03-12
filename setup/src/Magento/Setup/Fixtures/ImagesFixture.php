<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Framework\App\Filesystem\DirectoryList;

class ImagesFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 51;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Setup\Fixtures\ImagesGenerator\ImagesGeneratorFactory
     */
    private $imagesGeneratorFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    private $mediaConfig;

    /**
     * @param FixtureModel $fixtureModel
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        FixtureModel $fixtureModel,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Setup\Fixtures\ImagesGenerator\ImagesGeneratorFactory $imagesGeneratorFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig
    ) {
        parent::__construct($fixtureModel);

        $this->imagesGeneratorFactory = $imagesGeneratorFactory;
        $this->resourceConnection = $resourceConnection;
        $this->filesystem = $filesystem;
        $this->mediaConfig = $mediaConfig;
    }

    public function execute() {
        $imageNames = $this->generateImageFiles();
        $this->createImageEntities($imageNames);
    }

    public function getActionTitle() {
       return 'Generating images';
    }

    public function introduceParamLabels() {
        return [
            'images' => 'Images'
        ];
    }

    private function generateImageFiles()
    {
        $imageNames = [];

        /** @var \Magento\Setup\Fixtures\ImagesGenerator\ImagesGenerator $imagesGenerator */
        $imagesGenerator = $this->imagesGeneratorFactory->create();
        $imagesCount = 10;

        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $productImagesDirectoryPath = $mediaDirectory->getRelativePath($this->mediaConfig->getBaseMediaPath());

        for ($i=1; $i<=$imagesCount; $i++) {
            $imageName = md5($i) . '.jpg';
            $imageFullName = DIRECTORY_SEPARATOR . substr($imageName, 0, 1)
                . DIRECTORY_SEPARATOR . substr($imageName, 1, 1)
                . DIRECTORY_SEPARATOR . $imageName;

            $imagePath = $imagesGenerator->generate([
                'image-width' => 300,
                'image-height' => 300,
                'image-name' => $imageName
            ]);

            $mediaDirectory->renameFile(
                $mediaDirectory->getRelativePath($imagePath),
                $productImagesDirectoryPath . $imageFullName
            );

            $imageNames[] = $imageFullName;
        }

        return $imageNames;
    }

    private function createImageEntities()
    {
//                'media_gallery' => [
//                    'images' => [
//                        '1' => [
//                            'file' => "/n/a/naz.jpg",
//                            'media_type' => "image",
//                            'entity_id' => "1013",
//                            'label' => "",
//                            'position' => "1",
//                            'disabled' => "0",
//                            'position_default' => "1",
//                            'disabled_default' => "0",
//                        ]
//                    ]
//                ],
//                'image' => "/n/a/naz.jpg",
//                'small_image' => "/n/a/naz.jpg",
//                'thumbnail' => "/n/a/naz.jpg",
//                'swatch_image' => "/n/a/naz.jpg"
//        ]
    }

}
