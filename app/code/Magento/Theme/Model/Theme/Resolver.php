<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Theme\Model\Theme;

/**
 * Theme resolver model
 */
class Resolver implements \Magento\Framework\View\Design\Theme\ResolverInterface
{
    /**
     * @var array
     */
    private $resolvedThemes = [];

    /**
     * @var bool[]
     */
    private $isThemeResolved = [];

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $design;

    /**
     * @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory
     */
    protected $themeFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeFactory
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory $themeFactory
    ) {
        $this->design = $design;
        $this->themeFactory = $themeFactory;
        $this->appState = $appState;
    }

    /**
     * Retrieve instance of a theme currently used in an area
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     */
    public function get()
    {
        $area = $this->appState->getAreaCode();
        if ($this->isThemeResolved[$area] ?? false) {
            return $this->resolvedThemes[$area];
        }

        $designTheme = $this->design->getDesignTheme();
        if (($designTheme && $designTheme->getArea() == $area) || $this->design->getArea() == $area) {
            $result = $designTheme;
        } else {
            /** @var \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection */
            $themeCollection = $this->themeFactory->create();
            $themeIdentifier = $this->design->getConfigurationDesignTheme($area);
            if (is_numeric($themeIdentifier)) {
                $result = $themeCollection->getItemById($themeIdentifier);
            } else {
                $themeFullPath = $area
                    . \Magento\Framework\View\Design\ThemeInterface::PATH_SEPARATOR
                    . $themeIdentifier;
                $result = $themeCollection->getThemeByFullPath($themeFullPath);
            }
        }

        $this->resolvedThemes[$area] = $result;
        $this->isThemeResolved[$area] = true;

        return $result;
    }
}
