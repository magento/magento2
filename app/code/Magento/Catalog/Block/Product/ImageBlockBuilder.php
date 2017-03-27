<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

use Magento\Catalog\Model\View\Asset\ImageFactory as AssetImageFactory;
use Magento\Framework\View\ConfigInterface;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\View\Asset\Image as AssetImage;
use Magento\Catalog\Model\Product\Image\SizeCache;

/**
 * Used to build product image blocks in product list blocks.
 */
class ImageBlockBuilder
{
    /**
     * @var ConfigInterface
     */
    private $presentationConfig;

    /**
     * @var AssetImageFactory
     */
    private $viewAssetImageFactory;

    /**
     * @var ImageFactory
     */
    private $imageBlockFactory;

    /**
     * @var ParamsBuilder
     */
    private $imageParamsBuilder;

    /**
     * @var SizeCache
     */
    private $sizeCache;

    /**
     * @param ConfigInterface $presentationConfig
     * @param AssetImageFactory $viewAssetImageFactory
     * @param ImageFactory $imageBlockFactory
     * @param ParamsBuilder $imageParamsBuilder
     * @param SizeCache $sizeCache
     */
    public function __construct(
        ConfigInterface $presentationConfig,
        AssetImageFactory $viewAssetImageFactory,
        ImageFactory $imageBlockFactory,
        ParamsBuilder $imageParamsBuilder,
        SizeCache $sizeCache
    ) {
        $this->presentationConfig = $presentationConfig->getViewConfig();
        $this->viewAssetImageFactory = $viewAssetImageFactory;
        $this->imageBlockFactory = $imageBlockFactory;
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->sizeCache = $sizeCache;
    }

    /**
     * Get image size
     *
     * @param AssetImage $imageAsset
     * @return array
     * @throws \Exception
     */
    private function getImageSize(AssetImage $imageAsset)
    {
        $imagePath = $imageAsset->getPath();
        $size = $this->sizeCache->load($imagePath);
        if (!$size) {
            $size = getimagesize($imagePath);
            if (!$size) {
                throw new \Exception('An error occurred while reading file: ' . $imagePath);
            }
            $this->sizeCache->save($size[0], $size[1], $imagePath);
            $size = ['width' => $size[0], 'height' => $size[1]];
        }

        return $size;
    }

    /**
     * Build image block for product and for specific display area (product grid, list, etc)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $displayArea
     * @return Image
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function buildBlock($product, $displayArea)
    {
        $imageArguments = $this->presentationConfig->getMediaAttributes(
            'Magento_Catalog',
            'images',
            $displayArea
        );

        $image = $this->imageParamsBuilder->build($imageArguments);

        $type = isset($imageArguments['type']) ? $imageArguments['type'] : null;
        $baseFilePath = $product->getData($type);
        $baseFilePath = $baseFilePath === 'no_selection' ? null : $baseFilePath;

        $imageAsset = $this->viewAssetImageFactory->create(
            [
                'miscParams' => $image,
                'filePath' => $baseFilePath,
            ]
        );

        $label = $product->getData($imageArguments['type'] . '_' . 'label');
        if (empty($label)) {
            $label = $product->getName();
        }

        $frame = isset($imageArguments['frame']) ? $imageArguments ['frame'] : null;
        if (empty($frame)) {
            $frame = $this->presentationConfig->getVarValue('Magento_Catalog', 'product_image_white_borders');
        }

        $template = $frame
            ? 'Magento_Catalog::product/image.phtml'
            : 'Magento_Catalog::product/image_with_borders.phtml';

        $width = $image['image_width'];
        $height = $image['image_height'];

        try {
            $resizedInfo = $this->getImageSize($imageAsset);
        } catch (\Exception $e) {
            $resizedInfo['width'] = $width;
            $resizedInfo['height'] = $height;
        }

        $data = [
            'data' => [
                'template' => $template,
                'image_url' => $imageAsset->getUrl(),
                'width' => $width,
                'height' => $height,
                'label' => $label,
                'ratio' => ($width && $height) ? $height / $width : 1,
                'resized_image_width' => empty($resizedInfo['width']) ? $width : $resizedInfo['width'],
                'resized_image_height' => empty($resizedInfo['height']) ? $height : $resizedInfo['height'],
            ],
        ];

        return $this->imageBlockFactory->create($data);
    }
}
