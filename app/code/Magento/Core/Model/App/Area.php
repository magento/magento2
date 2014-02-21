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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Application area model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Model\App;

class Area
{
    const AREA_GLOBAL   = 'global';
    const AREA_FRONTEND = 'frontend';
    const AREA_ADMIN    = 'admin';

    const PART_CONFIG   = 'config';
    const PART_TRANSLATE= 'translate';
    const PART_DESIGN   = 'design';

    /**
     * Area parameter.
     */
    const PARAM_AREA = 'area';

    /**
     * Array of area loaded parts
     *
     * @var array
     */
    protected $_loadedParts;

    /**
     * Area code
     *
     * @var string
     */
    protected $_code;

    /**
     * Event Manager
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Translator
     *
     * @var \Magento\TranslateInterface
     */
    protected $_translator;

    /**
     * Application config
     *
     * @var \Magento\App\ConfigInterface
     */
    protected $_config;

    /**
     * Object manager
     *
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\App\ObjectManager\ConfigLoader
     */
    protected $_diConfigLoader;

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * Core design
     *
     * @var \Magento\Core\Model\Design
     */
    protected $_design;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\TranslateInterface $translator
     * @param \Magento\App\ConfigInterface $config
     * @param \Magento\ObjectManager $objectManager
     * @param \Magento\App\ObjectManager\ConfigLoader $diConfigLoader
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\Design $design
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param string $areaCode
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\TranslateInterface $translator,
        \Magento\App\ConfigInterface $config,
        \Magento\ObjectManager $objectManager,
        \Magento\App\ObjectManager\ConfigLoader $diConfigLoader,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\Design $design,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        $areaCode
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_code = $areaCode;
        $this->_config = $config;
        $this->_objectManager = $objectManager;
        $this->_diConfigLoader = $diConfigLoader;
        $this->_eventManager = $eventManager;
        $this->_translator = $translator;
        $this->_logger = $logger;
        $this->_design = $design;
        $this->_storeManager = $storeManager;
    }

    /**
     * Load area data
     *
     * @param   string|null $part
     * @return  $this
     */
    public function load($part=null)
    {
        if (is_null($part)) {
            $this->_loadPart(self::PART_CONFIG)
                ->_loadPart(self::PART_DESIGN)
                ->_loadPart(self::PART_TRANSLATE);
        } else {
            $this->_loadPart($part);
        }
        return $this;
    }

    /**
     * Detect and apply design for the area
     *
     * @param \Magento\App\RequestInterface $request
     * @return void
     */
    public function detectDesign($request = null)
    {
        if ($this->_code == self::AREA_FRONTEND) {
            $isDesignException = ($request && $this->_applyUserAgentDesignException($request));
            if (!$isDesignException) {
                $this->_design
                    ->loadChange($this->_storeManager->getStore()->getId())
                    ->changeDesign($this->_getDesign());
            }
        }
    }

    /**
     * Analyze user-agent information to override custom design settings
     *
     * @param \Magento\App\RequestInterface $request
     * @return bool
     */
    protected function _applyUserAgentDesignException($request)
    {
        $userAgent = $request->getServer('HTTP_USER_AGENT');
        if (empty($userAgent)) {
            return false;
        }
        try {
            $expressions = $this->_coreStoreConfig->getConfig('design/theme/ua_regexp');
            if (!$expressions) {
                return false;
            }
            $expressions = unserialize($expressions);
            foreach ($expressions as $rule) {
                if (preg_match($rule['regexp'], $userAgent)) {
                    $this->_getDesign()->setDesignTheme($rule['value']);
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->_logger->logException($e);
        }
        return false;
    }

    /**
     * @return \Magento\View\DesignInterface
     */
    protected function _getDesign()
    {
        return $this->_objectManager->get('Magento\View\DesignInterface');
    }

    /**
     * Loading part of area
     *
     * @param   string $part
     * @return  $this
     */
    protected function _loadPart($part)
    {
        if (isset($this->_loadedParts[$part])) {
            return $this;
        }
        \Magento\Profiler::start('load_area:' . $this->_code . '.' . $part,
            array('group' => 'load_area', 'area_code' => $this->_code, 'part' => $part));
        switch ($part) {
            case self::PART_CONFIG:
                $this->_initConfig();
                break;
            case self::PART_TRANSLATE:
                $this->_initTranslate();
                break;
            case self::PART_DESIGN:
                $this->_initDesign();
                break;
        }
        $this->_loadedParts[$part] = true;
        \Magento\Profiler::stop('load_area:' . $this->_code . '.' . $part);
        return $this;
    }

    /**
     * Load area configuration
     *
     * @return void
     */
    protected function _initConfig()
    {
        $this->_objectManager->configure($this->_diConfigLoader->load($this->_code));
    }

    /**
     * Initialize translate object.
     *
     * @return $this
     */
    protected function _initTranslate()
    {
        $dispatchResult = new \Magento\Object(array(
            'inline_type' => null,
            'params' => array('area' => $this->_code)
        ));
        $eventManager = $this->_objectManager->get('Magento\Event\ManagerInterface');
        $eventManager->dispatch('translate_initialization_before', array(
            'translate_object' => $this->_translator,
            'result' => $dispatchResult
        ));
        $this->_translator->init(null, $dispatchResult, false);

        \Magento\Phrase::setRenderer($this->_objectManager->get('Magento\Phrase\RendererInterface'));
        return $this;
    }

    /**
     * @return void
     */
    protected function _initDesign()
    {
        $this->_getDesign()->setArea($this->_code)->setDefaultDesignTheme();
    }
}
