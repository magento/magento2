<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model;

/**
 * Theme model class
 *
 * @method \Magento\View\Design\ThemeInterface save()
 * @method string getPackageCode()
 * @method string getParentThemePath()
 * @method string getParentId()
 * @method string getThemeTitle()
 * @method string getThemeVersion()
 * @method string getPreviewImage()
 * @method bool getIsFeatured()
 * @method int getThemeId()
 * @method int getType()
 * @method array getAssignedStores()
 * @method \Magento\Core\Model\Resource\Theme\Collection getCollection()
 * @method \Magento\View\Design\ThemeInterface setAssignedStores(array $stores)
 * @method \Magento\View\Design\ThemeInterface addData(array $data)
 * @method \Magento\View\Design\ThemeInterface setParentId(int $id)
 * @method \Magento\View\Design\ThemeInterface setParentTheme($parentTheme)
 * @method \Magento\View\Design\ThemeInterface setPackageCode(string $packageCode)
 * @method \Magento\View\Design\ThemeInterface setThemeCode(string $themeCode)
 * @method \Magento\View\Design\ThemeInterface setThemePath(string $themePath)
 * @method \Magento\View\Design\ThemeInterface setThemeVersion(string $themeVersion)
 * @method \Magento\View\Design\ThemeInterface setArea(string $area)
 * @method \Magento\View\Design\ThemeInterface setThemeTitle(string $themeTitle)
 * @method \Magento\View\Design\ThemeInterface setType(int $type)
 * @method \Magento\View\Design\ThemeInterface setCode(string $code)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Theme extends \Magento\Core\Model\AbstractModel implements \Magento\View\Design\ThemeInterface
{
    /**#@+
     * Theme types group
     */
    const TYPE_PHYSICAL = 0;
    const TYPE_VIRTUAL  = 1;
    const TYPE_STAGING  = 2;
    /**#@-*/

    /**
     * Filename of view configuration
     */
    const FILENAME_VIEW_CONFIG = 'view.xml';

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $_eventPrefix = 'theme';

    /**
     * {@inheritdoc}
     *
     * @var string
     */
    protected $_eventObject = 'theme';

    /**
     * @var \Magento\Core\Model\Theme\FlyweightFactory
     */
    protected $_themeFactory;

    /**
     * @var \Magento\Core\Model\Theme\Domain\Factory
     */
    protected $_domainFactory;

    /**
     * @var \Magento\Core\Model\Theme\ImageFactory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Core\Model\Theme\Validator
     */
    protected $_validator;

    /**
     * @var \Magento\Core\Model\Theme\Customization
     */
    protected $_customization;

    /**
     * @var \Magento\Core\Model\Theme\CustomizationFactory
     */
    protected $_customFactory;

    /**
     * All possible types of a theme
     *
     * @var array
     */
    public static $types = array(
        self::TYPE_PHYSICAL,
        self::TYPE_VIRTUAL,
        self::TYPE_STAGING,
    );

    /**
     * Initialize dependencies
     *
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Theme\FlyweightFactory $themeFactory
     * @param \Magento\Core\Model\Theme\Domain\Factory $domainFactory
     * @param \Magento\Core\Model\Theme\ImageFactory $imageFactory
     * @param \Magento\Core\Model\Theme\Validator $validator
     * @param \Magento\Core\Model\Theme\CustomizationFactory $customizationFactory
     * @param \Magento\Core\Model\Resource\Theme $resource
     * @param \Magento\Core\Model\Resource\Theme\Collection $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Theme\FlyweightFactory $themeFactory,
        \Magento\Core\Model\Theme\Domain\Factory $domainFactory,
        \Magento\Core\Model\Theme\ImageFactory $imageFactory,
        \Magento\Core\Model\Theme\Validator $validator,
        \Magento\Core\Model\Theme\CustomizationFactory $customizationFactory,
        \Magento\Core\Model\Resource\Theme $resource = null,
        \Magento\Core\Model\Resource\Theme\Collection $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_themeFactory = $themeFactory;
        $this->_domainFactory = $domainFactory;
        $this->_imageFactory = $imageFactory;
        $this->_validator = $validator;
        $this->_customFactory = $customizationFactory;

        $this->addData(array(
            'type' => self::TYPE_VIRTUAL,
            'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND
        ));
    }

    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('Magento\Core\Model\Resource\Theme');
    }

    /**
     * Get theme image model
     *
     * @return \Magento\Core\Model\Theme\Image
     */
    public function getThemeImage()
    {
        return $this->_imageFactory->create(array('theme' => $this));
    }

    /**
     * @return \Magento\Core\Model\Theme\Customization
     */
    public function getCustomization()
    {
        if ($this->_customization === null) {
            $this->_customization = $this->_customFactory->create(array('theme' => $this));
        }
        return $this->_customization;
    }

    /**
     * Check if theme is deletable
     *
     * @return bool
     */
    public function isDeletable()
    {
        return $this->isEditable();
    }

    /**
     * Check if theme is editable
     *
     * @return bool
     */
    public function isEditable()
    {
        return self::TYPE_PHYSICAL != $this->getType();
    }

    /**
     * Check if theme is virtual
     *
     * @return bool
     */
    public function isVirtual()
    {
        return $this->getType() == self::TYPE_VIRTUAL;
    }

    /**
     * Check if theme is physical
     *
     * @return bool
     */
    public function isPhysical()
    {
        return $this->getType() == self::TYPE_PHYSICAL;
    }

    /**
     * Check theme is visible in backend
     *
     * @return bool
     */
    public function isVisible()
    {
        return in_array($this->getType(), array(self::TYPE_PHYSICAL, self::TYPE_VIRTUAL));
    }

    /**
     * Check is theme has child virtual themes
     *
     * @return bool
     */
    public function hasChildThemes()
    {
        return (bool)$this->getCollection()
            ->addTypeFilter(\Magento\Core\Model\Theme::TYPE_VIRTUAL)
            ->addFieldToFilter('parent_id', array('eq' => $this->getId()))
            ->getSize();
    }

    /**
     * Retrieve theme instance representing the latest changes to a theme
     *
     * @return \Magento\Core\Model\Theme|null
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
     */
    public function getArea()
    {
        return $this->getData('area');
    }

    /**
     * {@inheritdoc}
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
     */
    public function getFullPath()
    {
        return $this->getThemePath() ? $this->getArea() . self::PATH_SEPARATOR . $this->getThemePath() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return (string)$this->getData('code');
    }

    /**
     * Get one of theme domain models
     *
     * @param int|null $type
     * @return \Magento\Core\Model\Theme\Domain\Virtual|\Magento\Core\Model\Theme\Domain\Staging
     * @throws \InvalidArgumentException
     */
    public function getDomainModel($type = null)
    {
        if ($type !== null && $type != $this->getType()) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid domain model "%s" requested for theme "%s" of type "%s"',
                $type,
                $this->getId(),
                $this->getType()
            ));
        }

        return $this->_domainFactory->create($this);
    }

    /**
     * Validate theme data
     *
     * @return \Magento\Core\Model\Theme
     * @throws \Magento\Core\Exception
     */
    protected function _validate()
    {
        if (!$this->_validator->validate($this)) {
            $messages = $this->_validator->getErrorMessages();
            throw new \Magento\Core\Exception(implode(PHP_EOL, reset($messages)));
        }
        return $this;
    }

    /**
     * Before theme save
     *
     * @return \Magento\Core\Model\Theme
     */
    protected function _beforeSave()
    {
        $this->_validate();
        return parent::_beforeSave();
    }

    /**
     * Update all relations after deleting theme
     *
     * @return $this
     */
    protected function _afterDelete()
    {
        $stagingVersion = $this->getStagingVersion();
        if ($stagingVersion) {
            $stagingVersion->delete();
        }
        $this->getCollection()->updateChildRelations($this);
        return parent::_afterDelete();
    }
}
