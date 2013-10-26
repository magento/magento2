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

/**
 * Emulation model
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\App;

class Emulation extends \Magento\Object
{
    /**
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * @var \Magento\Core\Model\StoreManager
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\Translate
     */
    protected $_translate;

    /**
     * @var \Magento\Core\Helper\Translate
     */
    protected $_helperTranslate;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * @var \Magento\Core\Model\Design
     */
    protected $_design;

    /**
     * @param \Magento\Core\Model\App $app
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\View\DesignInterface $viewDesign
     * @param \Magento\Core\Model\Design $design
     * @param \Magento\Core\Model\Translate $translate
     * @param \Magento\Core\Helper\Translate $helperTranslate
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\App $app,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\View\DesignInterface $viewDesign,
        \Magento\Core\Model\Design $design,
        \Magento\Core\Model\Translate $translate,
        \Magento\Core\Helper\Translate $helperTranslate,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\LocaleInterface $locale,
        array $data = array()
    ) {
        $this->_locale = $locale;
        parent::__construct($data);
        $this->_app = $app;
        $this->_storeManager = $storeManager;
        $this->_viewDesign = $viewDesign;
        $this->_design = $design;
        $this->_translate = $translate;
        $this->_helperTranslate = $helperTranslate;
        $this->_coreStoreConfig = $coreStoreConfig;
    }

    /**
     * Start environment emulation of the specified store
     *
     * Function returns information about initial store environment and emulates environment of another store
     *
     * @param integer $storeId
     * @param string $area
     * @param bool $emulateStoreInlineTranslation emulate inline translation of the specified store or just disable it
     *
     * @return \Magento\Object information about environment of the initial store
     */
    public function startEnvironmentEmulation($storeId, $area = \Magento\Core\Model\App\Area::AREA_FRONTEND,
        $emulateStoreInlineTranslation = false
    ) {
        if ($area === null) {
            $area = \Magento\Core\Model\App\Area::AREA_FRONTEND;
        }
        $initialTranslateInline = $emulateStoreInlineTranslation
            ? $this->_emulateInlineTranslation($storeId, $area)
            : $this->_emulateInlineTranslation();
        $initialDesign = $this->_emulateDesign($storeId, $area);
        // Current store needs to be changed right before locale change and after design change
        $this->_storeManager->setCurrentStore($storeId);
        $initialLocaleCode = $this->_emulateLocale($storeId, $area);

        $initialEnvironmentInfo = new \Magento\Object();
        $initialEnvironmentInfo->setInitialTranslateInline($initialTranslateInline)
            ->setInitialDesign($initialDesign)
            ->setInitialLocaleCode($initialLocaleCode);

        return $initialEnvironmentInfo;
    }

    /**
     * Stop enviromment emulation
     *
     * Function restores initial store environment
     *
     * @param \Magento\Object $initialEnvironmentInfo information about environment of the initial store
     *
     * @return \Magento\Core\Model\App\Emulation
     */
    public function stopEnvironmentEmulation(\Magento\Object $initialEnvironmentInfo)
    {
        $this->_restoreInitialInlineTranslation($initialEnvironmentInfo->getInitialTranslateInline());
        $initialDesign = $initialEnvironmentInfo->getInitialDesign();
        $this->_restoreInitialDesign($initialDesign);
        // Current store needs to be changed right before locale change and after design change
        $this->_storeManager->setCurrentStore($initialDesign['store']);
        $this->_restoreInitialLocale($initialEnvironmentInfo->getInitialLocaleCode(), $initialDesign['area']);
        return $this;
    }

    /**
     * Emulate inline translation of the specified store
     *
     * Function disables inline translation if $storeId is null
     *
     * @param integer|null $storeId
     * @param string $area
     *
     * @return boolean initial inline translation state
     */
    protected function _emulateInlineTranslation($storeId = null, $area = \Magento\Core\Model\App\Area::AREA_FRONTEND)
    {
        if (is_null($storeId)) {
            $newTranslateInline = false;
        } else {
            if ($area == \Magento\Core\Model\App\Area::AREA_ADMIN) {
                $newTranslateInline = $this->_coreStoreConfig->getConfigFlag('dev/translate_inline/active_admin', $storeId);
            } else {
                $newTranslateInline = $this->_coreStoreConfig->getConfigFlag('dev/translate_inline/active', $storeId);
            }
        }
        $translateInline = $this->_translate->getTranslateInline();
        $this->_translate->setTranslateInline($newTranslateInline);
        return $translateInline;
    }

    /**
     * Apply design of the specified store
     *
     * @param integer $storeId
     * @param string $area
     *
     * @return array initial design parameters(package, store, area)
     */
    protected function _emulateDesign($storeId, $area = \Magento\Core\Model\App\Area::AREA_FRONTEND)
    {
        $store = $this->_storeManager->getStore();
        $initialDesign = array(
            'area' => $this->_viewDesign->getArea(),
            'theme' => $this->_viewDesign->getDesignTheme(),
            'store' => $store
        );

        $storeTheme = $this->_viewDesign->getConfigurationDesignTheme($area, array('store' => $storeId));
        $this->_viewDesign->setDesignTheme($storeTheme, $area);

        if ($area == \Magento\Core\Model\App\Area::AREA_FRONTEND) {
            $designChange = $this->_design->loadChange($storeId);
            if ($designChange->getData()) {
                $this->_viewDesign->setDesignTheme($designChange->getDesign(), $area);
            }
        }

        return $initialDesign;
    }

    /**
     * Apply locale of the specified store
     *
     * @param integer $storeId
     * @param string $area
     *
     * @return string initial locale code
     */
    protected function _emulateLocale($storeId, $area = \Magento\Core\Model\App\Area::AREA_FRONTEND)
    {
        $initialLocaleCode = $this->_locale->getLocaleCode();
        $newLocaleCode = $this->_coreStoreConfig->getConfig(
            \Magento\Core\Model\LocaleInterface::XML_PATH_DEFAULT_LOCALE,
            $storeId
        );
        $this->_locale->setLocaleCode($newLocaleCode);
        $this->_helperTranslate->initTranslate($newLocaleCode, $area, true);
        return $initialLocaleCode;
    }

    /**
     * Restore initial inline translation state
     *
     * @param bool $initialTranslate
     *
     * @return \Magento\Core\Model\App\Emulation
     */
    protected function _restoreInitialInlineTranslation($initialTranslate)
    {
        $this->_translate->setTranslateInline($initialTranslate);
        return $this;
    }

    /**
     * Restore design of the initial store
     *
     * @param array $initialDesign
     *
     * @return \Magento\Core\Model\App\Emulation
     */
    protected function _restoreInitialDesign(array $initialDesign)
    {
        $this->_viewDesign->setDesignTheme($initialDesign['theme'], $initialDesign['area']);
        return $this;
    }

    /**
     * Restore locale of the initial store
     *
     * @param string $initialLocaleCode
     * @param string $initialArea
     *
     * @return \Magento\Core\Model\App\Emulation
     */
    protected function _restoreInitialLocale($initialLocaleCode, $initialArea = \Magento\Core\Model\App\Area::AREA_ADMIN)
    {
        $this->_app->getLocale()->setLocaleCode($initialLocaleCode);
        $this->_helperTranslate->initTranslate($initialLocaleCode, $initialArea, true);
        return $this;
    }
}
