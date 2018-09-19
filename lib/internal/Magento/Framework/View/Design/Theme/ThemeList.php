<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme filesystem collection
 */
class ThemeList extends \Magento\Framework\Data\Collection implements ListInterface
{
    /**
     * Area constraint type
     */
    const CONSTRAINT_AREA = 'area';

    /**
     * Vendor constraint type
     *
     * For example, "Magento" part for theme "frontend/Magento/blank"
     */
    const CONSTRAINT_VENDOR = 'vendor';

    /**
     * Theme name constraint type
     *
     * For example, "blank" part for theme "frontend/Magento/blank"
     */
    const CONSTRAINT_THEME_NAME = 'theme_name';

    /**
     * Model of collection item
     *
     * @var string
     */
    protected $_itemObjectClass = ThemeInterface::class;

    /**
     * @var \Magento\Framework\Config\ThemeFactory $themeConfigFactory
     */
    protected $themeConfigFactory;

    /**
     * Constraints for the collection loading
     *
     * @var array
     */
    private $constraints = [
        self::CONSTRAINT_AREA => [],
        self::CONSTRAINT_VENDOR => [],
        self::CONSTRAINT_THEME_NAME => [],
    ];

    /**
     * Theme package list
     *
     * @var ThemePackageList
     */
    private $themePackageList;

    /**
     * Factory for read directory
     *
     * @var ReadFactory
     */
    private $dirReadFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\Framework\Config\ThemeFactory $themeConfigFactory
     * @param ThemePackageList $themePackageList
     * @param ReadFactory $dirReadFactory
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\Framework\Config\ThemeFactory $themeConfigFactory,
        ThemePackageList $themePackageList,
        ReadFactory $dirReadFactory
    ) {
        parent::__construct($entityFactory);
        $this->themeConfigFactory = $themeConfigFactory;
        $this->themePackageList = $themePackageList;
        $this->dirReadFactory = $dirReadFactory;
    }

    /**
     * Add constraint for the collection loading
     *
     * See CONSTRAINT_* constants for supported types
     *
     * @param string $type
     * @param string $value
     * @return void
     */
    public function addConstraint($type, $value)
    {
        if (!isset($this->constraints[$type])) {
            throw new \UnexpectedValueException("Constraint '$type' is not supported");
        }
        if ($this->isLoaded()) {
            $this->clear();
        }
        $this->constraints[$type][] = $value;
        $this->constraints[$type] = array_unique($this->constraints[$type]);
    }

    /**
     * Reset constraints for the collection loading
     *
     * @return void
     */
    public function resetConstraints()
    {
        foreach (array_keys($this->constraints) as $key) {
            $this->constraints[$key] = [];
        }
    }

    /**
     * Check value against constraint
     *
     * @param string $constraintType
     * @param string $value
     * @return bool
     */
    private function isAcceptable($constraintType, $value)
    {
        return empty($this->constraints[$constraintType]) || in_array($value, $this->constraints[$constraintType]);
    }

    /**
     * Fill collection with theme model loaded from filesystem
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $themes = [];
        foreach ($this->themePackageList->getThemes() as $themePackage) {
            if ($this->isAcceptable(self::CONSTRAINT_AREA, $themePackage->getArea())
                && $this->isAcceptable(self::CONSTRAINT_VENDOR, $themePackage->getVendor())
                && $this->isAcceptable(self::CONSTRAINT_THEME_NAME, $themePackage->getName())
            ) {
                $themes[] = $themePackage;
            }
        }

        $this->_loadFromFilesystem($themes);
        $this->resetConstraints();
        $this->_updateRelations()
            ->_renderFilters()
            ->_clearFilters();

        return $this;
    }

    /**
     * Set all parent themes
     *
     * @return $this
     */
    protected function _updateRelations()
    {
        $themeItems = $this->getItems();
        /** @var $theme \Magento\Framework\DataObject|ThemeInterface */
        foreach ($themeItems as $theme) {
            $parentThemePath = $theme->getData('parent_theme_path');
            if ($parentThemePath) {
                $themePath = $theme->getArea() . ThemeInterface::PATH_SEPARATOR . $parentThemePath;
                if (isset($themeItems[$themePath])) {
                    $theme->setParentTheme($themeItems[$themePath]);
                }
            }
        }
        return $this;
    }

    /**
     * Load themes collection from file system
     *
     * @param ThemePackage[] $themes
     * @return $this
     */
    protected function _loadFromFilesystem(array $themes)
    {
        foreach ($themes as $themePackage) {
            $theme = $this->getNewEmptyItem()->addData($this->_prepareConfigurationData($themePackage));
            $this->addItem($theme);
        }
        $this->_setIsLoaded();

        return $this;
    }

    /**
     * Return default path related data
     *
     * @param ThemePackage $themePackage
     * @return array
     */
    protected function _preparePathData($themePackage)
    {
        return [
            'theme_path_pieces' => [
                $themePackage->getVendor(),
                $themePackage->getName(),
            ]
        ];
    }

    /**
     * Return default configuration data
     *
     * @param ThemePackage $themePackage
     * @return array
     */
    protected function _prepareConfigurationData($themePackage)
    {
        $themeConfig = $this->_getConfigModel($themePackage);
        $pathData = $this->_preparePathData($themePackage);
        $media = $themeConfig->getMedia();

        $parentPathPieces = $themeConfig->getParentTheme();
        if (is_array($parentPathPieces) && count($parentPathPieces) == 1) {
            $pathPieces = $pathData['theme_path_pieces'];
            array_pop($pathPieces);
            $parentPathPieces = array_merge($pathPieces, $parentPathPieces);
        }

        $themePath = implode(ThemeInterface::PATH_SEPARATOR, $pathData['theme_path_pieces']);
        $themeCode = implode(ThemeInterface::CODE_SEPARATOR, $pathData['theme_path_pieces']);
        $parentPath = $parentPathPieces ? implode(ThemeInterface::PATH_SEPARATOR, $parentPathPieces) : null;

        return [
            'parent_id' => null,
            'type' => ThemeInterface::TYPE_PHYSICAL,
            'area' => $themePackage->getArea(),
            'theme_path' => $themePath,
            'code' => $themeCode,
            'theme_title' => $themeConfig->getThemeTitle(),
            'preview_image' => $media['preview_image'] ? $media['preview_image'] : null,
            'parent_theme_path' => $parentPath
        ];
    }

    /**
     * Apply set field filters
     *
     * @return $this
     */
    protected function _renderFilters()
    {
        $filters = $this->getFilter([]);
        /** @var $theme ThemeInterface */
        foreach ($this->getItems() as $itemKey => $theme) {
            $removeItem = false;
            foreach ($filters as $filter) {
                if ($filter['type'] == 'and' && $theme->getDataUsingMethod($filter['field']) != $filter['value']) {
                    $removeItem = true;
                }
            }
            if ($removeItem) {
                $this->removeItemByKey($itemKey);
            }
        }
        return $this;
    }

    /**
     * Clear all added filters
     *
     * @return $this
     */
    protected function _clearFilters()
    {
        $this->_filters = [];
        return $this;
    }

    /**
     * Return configuration model for the theme
     *
     * @param ThemePackage $themePackage
     * @return \Magento\Framework\Config\Theme
     */
    protected function _getConfigModel($themePackage)
    {
        $themeDir = $this->dirReadFactory->create($themePackage->getPath());
        if ($themeDir->isExist('theme.xml')) {
            $configContent = $themeDir->readFile('theme.xml');
        } else {
            $configContent = '';
        }
        return $this->themeConfigFactory->create(['configContent' => $configContent]);
    }

    /**
     * Retrieve item id
     *
     * @param \Magento\Framework\DataObject $item
     * @return string
     */
    protected function _getItemId(\Magento\Framework\DataObject $item)
    {
        return $item->getFullPath();
    }

    /**
     * Return array for select field
     *
     * @param bool $addEmptyField
     * @return array
     */
    public function toOptionArray($addEmptyField = false)
    {
        $optionArray = $addEmptyField ? ['' => ''] : [];
        return $optionArray + $this->_toOptionArray('theme_id', 'theme_title');
    }

    /**
     * Checks that a theme present in filesystem collection
     *
     * @param ThemeInterface $theme
     * @return bool
     */
    public function hasTheme(ThemeInterface $theme)
    {
        $themeItems = $this->getItems();
        return $theme->getThemePath() && isset($themeItems[$theme->getFullPath()]);
    }

    /**
     * Get theme from file system by area and theme_path
     *
     * @param string $fullPath
     * @return ThemeInterface
     */
    public function getThemeByFullPath($fullPath)
    {
        list($area, $themePath) = explode('/', $fullPath, 2);
        $this->addConstraint(self::CONSTRAINT_AREA, $area);
        $this->addFilter('theme_path', $themePath);

        return $this->getFirstItem();
    }
}
