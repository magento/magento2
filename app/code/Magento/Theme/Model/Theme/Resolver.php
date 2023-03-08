<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme;

use Magento\Framework\App\State;
use Magento\Framework\View\Design\Theme\ResolverInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory;

/**
 * Theme resolver model
 */
class Resolver implements ResolverInterface
{
    /**
     * @param State $appState
     * @param DesignInterface $design
     * @param CollectionFactory $themeFactory
     */
    public function __construct(
        protected readonly State $appState,
        protected readonly DesignInterface $design,
        protected readonly CollectionFactory $themeFactory
    ) {
    }

    /**
     * Retrieve instance of a theme currently used in an area
     *
     * @return ThemeInterface
     */
    public function get()
    {
        $area = $this->appState->getAreaCode();
        if ($this->design->getDesignTheme()->getArea() == $area || $this->design->getArea() == $area) {
            return $this->design->getDesignTheme();
        }

        /** @var ThemeCollection $themeCollection */
        $themeCollection = $this->themeFactory->create();
        $themeIdentifier = $this->design->getConfigurationDesignTheme($area);
        if (is_numeric($themeIdentifier)) {
            $result = $themeCollection->getItemById($themeIdentifier);
        } else {
            $themeFullPath = $area . ThemeInterface::PATH_SEPARATOR . $themeIdentifier;
            $result = $themeCollection->getThemeByFullPath($themeFullPath);
        }
        return $result;
    }
}
