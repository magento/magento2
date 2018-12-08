<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Image;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
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
     * @var PlaceholderFactory
     */
    private $placeholderFactory;

    /**
     * @param ConfigInterface $presentationConfig
     * @param ParamsBuilder $imageParamsBuilder
     * @param ImageFactory $viewAssetImageFactory
     * @param PlaceholderFactory $placeholderFactory
     */
    public function __construct(
        ConfigInterface $presentationConfig,
        ParamsBuilder $imageParamsBuilder,
        ImageFactory $viewAssetImageFactory,
        PlaceholderFactory $placeholderFactory
    ) {
        $this->presentationConfig = $presentationConfig;
        $this->imageParamsBuilder = $imageParamsBuilder;
        $this->viewAssetImageFactory = $viewAssetImageFactory;
        $this->placeholderFactory = $placeholderFactory;
    }

    /**
     * Build image url using base path and params
     *
     * @param string $baseFilePath
     * @param string $imageDisplayArea
     * @return string
     */
    public function getUrl(string $baseFilePath, string $imageDisplayArea): string
    {
        $imageArguments = $this->presentationConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            Image::MEDIA_TYPE_CONFIG_NODE,
            $imageDisplayArea
        );

        $imageMiscParams = $this->imageParamsBuilder->build($imageArguments);

        if ($baseFilePath === null || $baseFilePath === 'no_selection') {
            $asset = $this->placeholderFactory->create(
                [
                    'type' => $imageMiscParams['image_type']
                ]
            );
        } else {
            $asset = $this->viewAssetImageFactory->create(
                [
                    'miscParams' => $imageMiscParams,
                    'filePath' => $baseFilePath,
                ]
            );
        }

        return $asset->getUrl();
    }
}
