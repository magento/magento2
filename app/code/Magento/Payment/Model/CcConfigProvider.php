<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Source;

/**
 * Class CcConfigProvider
 * @api
 */
class CcConfigProvider implements ConfigProviderInterface
{
    /**
     * @var array
     */
    private $icons = [];

    /**
     * @var CcConfig
     */
    protected $ccConfig;

    /**
     * @var \Magento\Framework\View\Asset\Source
     */
    protected $assetSource;

    /**
     * @param CcConfig $ccConfig
     * @param Source $assetSource
     */
    public function __construct(
        CcConfig $ccConfig,
        Source $assetSource
    ) {
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'ccform' => [
                    'icons' => $this->getIcons()
                ]
            ]
        ];
    }

    /**
     * Get icons for available payment methods
     *
     * @return array
     */
    public function getIcons()
    {
        if (!empty($this->icons)) {
            return $this->icons;
        }

        $types = $this->ccConfig->getCcAvailableTypes();
        foreach (array_keys($types) as $code) {
            if (!array_key_exists($code, $this->icons)) {
                $asset = $this->ccConfig->createAsset('Magento_Payment::images/cc/' . strtolower($code) . '.png');
                $placeholder = $this->assetSource->findSource($asset);
                if ($placeholder) {
                    list($width, $height) = getimagesize($asset->getSourceFile());
                    $this->icons[$code] = [
                        'url' => $asset->getUrl(),
                        'width' => $width,
                        'height' => $height
                    ];
                }
            }
        }

        return $this->icons;
    }
}
