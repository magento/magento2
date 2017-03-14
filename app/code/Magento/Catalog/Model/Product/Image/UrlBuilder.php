<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image;

use Magento\Framework\View\ConfigInterface;
use Magento\Catalog\Model\View\Asset\ImageFactory;

/**
 * Used to build product image url
 */
class UrlBuilder
{
    /**
     * @var ConfigInterface
     */
    private $presentationConfig;

    /**
     * @var ImageFactory
     */
    private $viewAssetImageFactory;

    /**
     * @var ParamsBuilder
     */
    private $imageParamsBuilder;

    /**
     * @param ConfigInterface $presentationConfig
     * @param ParamsBuilder $imageParamsBuilder
     * @param ImageFactory $viewAssetImageFactory
     */
    public function __construct(
        ConfigInterface $presentationConfig,
        ParamsBuilder $imageParamsBuilder,
        ImageFactory $viewAssetImageFactory
    ) {
        $this->presentationConfig = $presentationConfig->getViewConfig();
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->viewAssetImageFactory = $viewAssetImageFactory;
    }

    /**
     * Build image url using base path and params
     *
     * @param string $baseFilePath
     * @param string $imageDisplayArea
     * @return string
     */
    public function getUrl($baseFilePath, $imageDisplayArea)
    {
        $imageArguments = $this->presentationConfig->getMediaAttributes(
            'Magento_Catalog',
            'images',
            $imageDisplayArea
        );

        $image = $this->imageParamsBuilder->build($imageArguments);

        $asset = $this->viewAssetImageFactory->create(
            [
                'miscParams' => $image,
                'filePath' => $baseFilePath,
            ]
        );
        return $asset->getUrl();
    }
}
