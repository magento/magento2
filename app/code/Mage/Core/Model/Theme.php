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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme model class
 *
 * @method Mage_Core_Model_Theme save()
 * @method string getPackageCode()
 * @method string getParentThemePath()
 * @method string getParentId()
 * @method string getThemeTitle()
 * @method string getThemeVersion()
 * @method string getPreviewImage()
 * @method string getMagentoVersionFrom()
 * @method string getMagentoVersionTo()
 * @method bool getIsFeatured()
 * @method int getThemeId()
 * @method int getType()
 * @method array getAssignedStores()
 * @method Mage_Core_Model_Resource_Theme_Collection getCollection()
 * @method Mage_Core_Model_Theme setAssignedStores(array $stores)
 * @method Mage_Core_Model_Theme addData(array $data)
 * @method Mage_Core_Model_Theme setParentId(int $id)
 * @method Mage_Core_Model_Theme setParentTheme($parentTheme)
 * @method Mage_Core_Model_Theme setPackageCode(string $packageCode)
 * @method Mage_Core_Model_Theme setThemeCode(string $themeCode)
 * @method Mage_Core_Model_Theme setThemePath(string $themePath)
 * @method Mage_Core_Model_Theme setThemeVersion(string $themeVersion)
 * @method Mage_Core_Model_Theme setArea(string $area)
 * @method Mage_Core_Model_Theme setThemeTitle(string $themeTitle)
 * @method Mage_Core_Model_Theme setMagentoVersionFrom(string $versionFrom)
 * @method Mage_Core_Model_Theme setMagentoVersionTo(string $versionTo)
 * @method Mage_Core_Model_Theme setType(int $type)
 * @method Mage_Core_Model_Theme setCode(string $code)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Core_Model_Theme extends Mage_Core_Model_Abstract implements Mage_Core_Model_ThemeInterface
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
     * @var Mage_Core_Model_Theme_FlyweightFactory
     */
    protected $_themeFactory;

    /**
     * @var Mage_Core_Model_Theme_Domain_Factory
     */
    protected $_domainFactory;

    /**
     * @var Mage_Core_Model_Theme_ImageFactory
     */
    protected $_imageFactory;

    /**
     * @var Mage_Core_Model_Theme_Validator
     */
    protected $_validator;

    /**
     * @var Mage_Core_Model_Theme_Customization
     */
    protected $_customization;

    /**
     * @var Mage_Core_Model_Theme_CustomizationFactory
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
     * @param Mage_Core_Model_Context $context
     * @param Mage_Core_Model_Theme_FlyweightFactory $themeFactory
     * @param Mage_Core_Model_Theme_Domain_Factory $domainFactory
     * @param Mage_Core_Model_Theme_ImageFactory $imageFactory
     * @param Mage_Core_Model_Theme_Validator $validator
     * @param Mage_Core_Model_Theme_CustomizationFactory $customizationFactory
     * @param Mage_Core_Model_Resource_Theme $resource
     * @param Mage_Core_Model_Resource_Theme_Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Mage_Core_Model_Context $context,
        Mage_Core_Model_Theme_FlyweightFactory $themeFactory,
        Mage_Core_Model_Theme_Domain_Factory $domainFactory,
        Mage_Core_Model_Theme_ImageFactory $imageFactory,
        Mage_Core_Model_Theme_Validator $validator,
        Mage_Core_Model_Theme_CustomizationFactory $customizationFactory,
        Mage_Core_Model_Resource_Theme $resource = null,
        Mage_Core_Model_Resource_Theme_Collection $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct($context, $resource, $resourceCollection, $data);
        $this->_themeFactory = $themeFactory;
        $this->_domainFactory = $domainFactory;
        $this->_imageFactory = $imageFactory;
        $this->_validator = $validator;
        $this->_customFactory = $customizationFactory;

        $this->addData(array(
            'type' => self::TYPE_VIRTUAL,
            'area' => Mage_Core_Model_App_Area::AREA_FRONTEND
        ));
    }

    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('Mage_Core_Model_Resource_Theme');
    }

    /**
     * Get theme image model
     *
     * @return Mage_Core_Model_Theme_Image
     */
    public function getThemeImage()
    {
        return $this->_imageFactory->create(array('theme' => $this));
    }

    /**
     * @return Mage_Core_Model_Theme_Customization
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
            ->addTypeFilter(Mage_Core_Model_Theme::TYPE_VIRTUAL)
            ->addFieldToFilter('parent_id', array('eq' => $this->getId()))
            ->getSize();
    }

    /**
     * Retrieve theme instance representing the latest changes to a theme
     *
     * @return Mage_Core_Model_Theme|null
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
     * Check if the theme is compatible with Magento version
     *
     * @return bool
     */
    public function isThemeCompatible()
    {
        $magentoVersion = Mage::getVersion();
        if (version_compare($magentoVersion, $this->getMagentoVersionFrom(), '>=')) {
            if ($this->getMagentoVersionTo() == '*'
                || version_compare($magentoVersion, $this->getMagentoVersionFrom(), '<=')
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get one of theme domain models
     *
     * @param int|null $type
     * @return Mage_Core_Model_Theme_Domain_Virtual|Mage_Core_Model_Theme_Domain_Staging
     * @throws InvalidArgumentException
     */
    public function getDomainModel($type = null)
    {
        if ($type !== null && $type != $this->getType()) {
            throw new InvalidArgumentException(sprintf(
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
     * @return Mage_Core_Model_Theme
     * @throws Mage_Core_Exception
     */
    protected function _validate()
    {
        if (!$this->_validator->validate($this)) {
            $messages = $this->_validator->getErrorMessages();
            throw new Mage_Core_Exception(implode(PHP_EOL, reset($messages)));
        }
        return $this;
    }

    /**
     * Before theme save
     *
     * @return Mage_Core_Model_Theme
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
