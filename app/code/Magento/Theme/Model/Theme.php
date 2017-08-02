<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;

/**
 * Theme model class
 *
 * @method string getPackageCode()
 * @method string getParentThemePath()
 * @method string getParentId()
 * @method string getThemeTitle()
 * @method string getPreviewImage()
 * @method bool getIsFeatured()
 * @method int getThemeId()
 * @method int getType()
 * @method array getAssignedStores()
 * @method ThemeInterface setAssignedStores(array $stores)
 * @method ThemeInterface setParentId(int $id)
 * @method ThemeInterface setParentTheme($parentTheme)
 * @method ThemeInterface setPackageCode(string $packageCode)
 * @method ThemeInterface setThemeCode(string $themeCode)
 * @method ThemeInterface setThemePath(string $themePath)
 * @method ThemeInterface setThemeTitle(string $themeTitle)
 * @method ThemeInterface setType(int $type)
 * @method ThemeInterface setCode(string $code)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Theme extends \Magento\Framework\Model\AbstractModel implements ThemeInterface
{
    /**
     * {@inheritdoc}
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'theme';

    /**
     * {@inheritdoc}
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'theme';

    /**
     * @var \Magento\Framework\View\Design\Theme\FlyweightFactory
     * @since 2.0.0
     */
    protected $_themeFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\Domain\Factory
     * @since 2.0.0
     */
    protected $_domainFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\ImageFactory
     * @since 2.0.0
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\Validator
     * @since 2.0.0
     */
    protected $_validator;

    /**
     * @var \Magento\Framework\View\Design\Theme\Customization
     * @since 2.0.0
     */
    protected $_customization;

    /**
     * @var \Magento\Framework\View\Design\Theme\CustomizationFactory
     * @since 2.0.0
     */
    protected $_customFactory;

    /**
     * @var ThemeFactory
     * @since 2.2.0
     */
    private $themeModelFactory;

    /**
     * @var ThemeInterface[]
     * @since 2.0.0
     */
    protected $inheritanceSequence;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory
     * @param \Magento\Framework\View\Design\Theme\Domain\Factory $domainFactory
     * @param \Magento\Framework\View\Design\Theme\ImageFactory $imageFactory
     * @param \Magento\Framework\View\Design\Theme\Validator $validator
     * @param \Magento\Framework\View\Design\Theme\CustomizationFactory $customizationFactory
     * @param \Magento\Theme\Model\ResourceModel\Theme $resource
     * @param \Magento\Theme\Model\ResourceModel\Theme\Collection $resourceCollection
     * @param array $data
     * @param ThemeFactory $themeModelFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory,
        \Magento\Framework\View\Design\Theme\Domain\Factory $domainFactory,
        \Magento\Framework\View\Design\Theme\ImageFactory $imageFactory,
        \Magento\Framework\View\Design\Theme\Validator $validator,
        \Magento\Framework\View\Design\Theme\CustomizationFactory $customizationFactory,
        \Magento\Theme\Model\ResourceModel\Theme $resource = null,
        ThemeCollection $resourceCollection = null,
        array $data = [],
        ThemeFactory $themeModelFactory = null
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_themeFactory = $themeFactory;
        $this->_domainFactory = $domainFactory;
        $this->_imageFactory = $imageFactory;
        $this->_validator = $validator;
        $this->_customFactory = $customizationFactory;
        $this->themeModelFactory = $themeModelFactory ?: ObjectManager::getInstance()->get(ThemeFactory::class);
        $this->addData(['type' => self::TYPE_VIRTUAL]);
    }

    /**
     * Init resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Theme\Model\ResourceModel\Theme::class);
    }

    /**
     * Get theme image model
     *
     * @return \Magento\Framework\View\Design\Theme\Image
     * @since 2.0.0
     */
    public function getThemeImage()
    {
        return $this->_imageFactory->create(['theme' => $this]);
    }

    /**
     * @return \Magento\Framework\View\Design\Theme\Customization
     * @since 2.0.0
     */
    public function getCustomization()
    {
        if ($this->_customization === null) {
            $this->_customization = $this->_customFactory->create(['theme' => $this]);
        }
        return $this->_customization;
    }

    /**
     * Check if theme is deletable
     *
     * @return bool
     * @since 2.0.0
     */
    public function isDeletable()
    {
        return $this->isEditable();
    }

    /**
     * Check if theme is editable
     *
     * @return bool
     * @since 2.0.0
     */
    public function isEditable()
    {
        return self::TYPE_PHYSICAL != $this->getType();
    }

    /**
     * Check if theme is virtual
     *
     * @return bool
     * @since 2.0.0
     */
    public function isVirtual()
    {
        return $this->getType() == self::TYPE_VIRTUAL;
    }

    /**
     * Check if theme is physical
     *
     * @return bool
     * @since 2.0.0
     */
    public function isPhysical()
    {
        return $this->getType() == self::TYPE_PHYSICAL;
    }

    /**
     * Check theme is visible in backend
     *
     * @return bool
     * @since 2.0.0
     */
    public function isVisible()
    {
        return in_array($this->getType(), [self::TYPE_PHYSICAL, self::TYPE_VIRTUAL]);
    }

    /**
     * Check is theme has child virtual themes
     *
     * @return bool
     * @since 2.0.0
     */
    public function hasChildThemes()
    {
        return (bool)$this->getCollection()->addTypeFilter(
            self::TYPE_VIRTUAL
        )->addFieldToFilter(
            'parent_id',
            ['eq' => $this->getId()]
        )->getSize();
    }

    /**
     * Retrieve theme instance representing the latest changes to a theme
     *
     * @return Theme|null
     * @since 2.0.0
     */
    public function getStagingVersion()
    {
        if ($this->getId()) {
            $collection = $this->getCollection();
            $collection->addFieldToFilter('parent_id', $this->getId());
            $collection->addFieldToFilter('type', self::TYPE_STAGING);
            $stagingTheme = $collection->getFirstItem();
            if ($stagingTheme->getId()) {
                return $stagingTheme;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getParentTheme()
    {
        if ($this->hasData('parent_theme')) {
            return $this->getData('parent_theme');
        }

        $theme = null;
        if ($this->getParentId()) {
            $theme = $this->_themeFactory->create($this->getParentId());
        }
        $this->setParentTheme($theme);
        return $theme;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getArea()
    {
        // In order to support environment emulation of area, if area is set, return it
        if ($this->getData('area') && !$this->_appState->isAreaCodeEmulated()) {
            return $this->getData('area');
        }
        return $this->_appState->getAreaCode();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getThemePath()
    {
        return $this->getData('theme_path');
    }

    /**
     * Retrieve theme full path which is used to distinguish themes if they are not in DB yet
     *
     * Alternative id looks like "<area>/<theme_path>".
     * Used as id in file-system theme collection
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getFullPath()
    {
        return $this->getThemePath() ? $this->getArea() . self::PATH_SEPARATOR . $this->getThemePath() : null;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCode()
    {
        return (string)$this->getData('code');
    }

    /**
     * Get one of theme domain models
     *
     * @param int|null $type
     * @return \Magento\Theme\Model\Theme\Domain\Virtual|\Magento\Theme\Model\Theme\Domain\Staging
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function getDomainModel($type = null)
    {
        if ($type !== null && $type != $this->getType()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid domain model "%s" requested for theme "%s" of type "%s"',
                    $type,
                    $this->getId(),
                    $this->getType()
                )
            );
        }

        return $this->_domainFactory->create($this);
    }

    /**
     * Validate theme data
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function _validate()
    {
        if (!$this->_validator->validate($this)) {
            $messages = $this->_validator->getErrorMessages();
            throw new \Magento\Framework\Exception\LocalizedException(__(implode(PHP_EOL, reset($messages))));
        }
        return $this;
    }

    /**
     * Before theme save
     *
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave()
    {
        $this->_validate();
        return parent::beforeSave();
    }

    /**
     * Update all relations after deleting theme
     *
     * @return $this
     * @since 2.0.0
     */
    public function afterDelete()
    {
        $stagingVersion = $this->getStagingVersion();
        if ($stagingVersion) {
            $stagingVersion->delete();
        }
        $this->getCollection()->updateChildRelations($this);
        return parent::afterDelete();
    }

    /**
     * Return the full theme inheritance sequence, from the root theme till a specified one
     *
     * @return ThemeInterface[]
     * @since 2.0.0
     */
    public function getInheritedThemes()
    {
        if (null === $this->inheritanceSequence) {
            $theme = $this;
            $result = [];
            while ($theme) {
                $result[] = $theme;
                $theme = $theme->getParentTheme();
            }
            $this->inheritanceSequence = array_reverse($result);
        }
        return $this->inheritanceSequence;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function toArray(array $keys = [])
    {
        $data = parent::toArray($keys);
        if (isset($data['parent_theme'])) {
            $data['parent_theme'] = $this->getParentTheme()->toArray();
        }

        if (isset($data['inherited_themes'])) {
            foreach ($data['inherited_themes'] as $key => $inheritedTheme) {
                $data['inherited_themes'][$key] = $inheritedTheme->toArray();
            }
        }

        return $data;
    }

    /**
     * Populate Theme object from an array
     *
     * @param array $data
     * @return Theme
     * @since 2.2.0
     */
    public function populateFromArray(array $data)
    {
        $this->_data = $data;
        if (isset($data['parent_theme'])) {
            $this->_data['parent_theme'] = $this->createThemeInstance()->populateFromArray($data['parent_theme']);
        }

        if (isset($data['inherited_themes'])) {
            foreach ($data['inherited_themes'] as $key => $inheritedTheme) {
                $themeInstance = $this->createThemeInstance()->populateFromArray($inheritedTheme);
                $this->_data['inherited_themes'][$key] = $themeInstance;
            }
        }

        return $this;
    }

    /**
     * Create Theme instance
     *
     * @return \Magento\Theme\Model\Theme
     * @since 2.2.0
     */
    private function createThemeInstance()
    {
        return $this->themeModelFactory->create();
    }
}
