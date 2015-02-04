<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product;
use Magento\Theme\Model\Resource\Theme\Collection as ThemeCollection;
use Magento\Framework\App\Area;
use Magento\Framework\View\ConfigInterface;

class Cache
{
    /**
     * @var ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var ThemeCollection
     */
    protected $themeCollection;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param ConfigInterface $viewConfig
     * @param ThemeCollection $themeCollection
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        ConfigInterface $viewConfig,
        ThemeCollection $themeCollection,
        ImageHelper $imageHelper
    ) {
        $this->viewConfig = $viewConfig;
        $this->themeCollection = $themeCollection;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Retrieve view configuration data
     *
     * Collect data for 'Magento_Catalog' module from /etc/view.xml files.
     *
     * @return array
     */
    protected function getData()
    {
        if (!$this->data) {
            foreach ($this->themeCollection->loadRegisteredThemes() as $theme) {
                $config = $this->viewConfig->getViewConfig([
                    'area' => Area::AREA_FRONTEND,
                    'themeModel' => $theme,
                ]);
                $this->data = array_merge(
                    $this->data,
                    $this->applyFilters($config->getVars('Magento_Catalog'))
                );
            }
        }
        return $this->data;
    }

    /**
     * Filter data with well formed syntax (name:parameter)
     *
     * @param array $vars
     * @return array
     */
    protected function getWellFormed(array $vars)
    {
        if (empty($vars)) {
            return [];
        }
        $filtered = array_filter(array_keys($vars), function ($key) {
            return stripos($key, ':') !== false;
        });
        return array_intersect_key($vars, array_flip($filtered));
    }

    /**
     * Convert flat data array to tree-like data array
     *
     * @param array $data
     * @return array
     */
    protected function convertToTree(array $data)
    {
        $result = [];
        foreach ($data as $name => $value) {
            list($identifier, $parameter) = explode(':', $name);
            $result[$identifier][$parameter] = $value;
        }
        return $result;
    }

    /**
     * Check data integrity and unify data array items
     *
     * @param array $data
     * @return array
     */
    protected function getUnique(array $data)
    {
        $result = [];
        foreach ($data as $item) {
            if (!isset($item['type'])
                || !isset($item['width'])
                || !isset($item['height'])) {
                continue;
            }
            $newItem = [
                'type'   => $item['type'],
                'width'  => $item['width'],
                'height' => $item['height'],
            ];
            $result[implode('_', $newItem)] = $newItem;
        }
        return $result;
    }

    /**
     * Apply filters and converters to config data
     *
     * @param array $data
     * @return array
     */
    protected function applyFilters(array $data)
    {
        $data = $this->getWellFormed($data);
        $data = $this->convertToTree($data);
        return $this->getUnique($data);
    }

    /**
     * Resize product images and save results to image cache
     *
     * @param Product $product
     * @return $this
     */
    public function generate(Product $product)
    {
        $galleryImages = $product->getMediaGalleryImages();
        if ($galleryImages) {
            foreach ($galleryImages as $image) {
                foreach ($this->getData() as $params) {
                    $this->imageHelper->init($product, $params['type'], $image->getFile())
                        ->resize($params['width'], $params['height'])
                        ->save();
                }
            }
        }
        return $this;
    }
}
