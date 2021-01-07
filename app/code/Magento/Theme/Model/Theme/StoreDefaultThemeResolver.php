<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Theme;

use Magento\Framework\App\Area;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;

/**
 * Store default theme resolver.
 *
 * Use system config fallback mechanism if no theme is directly assigned to the store-view.
 */
class StoreDefaultThemeResolver implements StoreThemesResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $themeCollectionFactory;
    /**
     * @var DesignInterface
     */
    private $design;
    /**
     * @var ThemeInterface[]
     */
    private $registeredThemes;

    /**
     * @param CollectionFactory $themeCollectionFactory
     * @param DesignInterface $design
     */
    public function __construct(
        CollectionFactory $themeCollectionFactory,
        DesignInterface $design
    ) {
        $this->design = $design;
        $this->themeCollectionFactory = $themeCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getThemes(StoreInterface $store): array
    {
        $theme = $this->design->getConfigurationDesignTheme(
            Area::AREA_FRONTEND,
            ['store' => $store]
        );
        $themes = [];
        if ($theme) {
            if (!is_numeric($theme)) {
                $registeredThemes = $this->getRegisteredThemes();
                if (isset($registeredThemes[$theme])) {
                    $themes[] = $registeredThemes[$theme]->getId();
                }
            } else {
                $themes[] = $theme;
            }
        }
        return $themes;
    }

    /**
     * Get system registered themes.
     *
     * @return ThemeInterface[]
     */
    private function getRegisteredThemes(): array
    {
        if ($this->registeredThemes === null) {
            $this->registeredThemes = [];
            /** @var \Magento\Theme\Model\ResourceModel\Theme\Collection $collection */
            $collection = $this->themeCollectionFactory->create();
            $themes = $collection->loadRegisteredThemes();
            /** @var ThemeInterface $theme */
            foreach ($themes as $theme) {
                $this->registeredThemes[$theme->getCode()] = $theme;
            }
        }
        return $this->registeredThemes;
    }
}
