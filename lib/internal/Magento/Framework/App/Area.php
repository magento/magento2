<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\ObjectManager\ConfigLoaderInterface;

/**
 * Application area model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Area implements \Magento\Framework\App\AreaInterface
{
    const AREA_GLOBAL = 'global';
    const AREA_FRONTEND = 'frontend';
    const AREA_ADMINHTML = 'adminhtml';
    const AREA_CRONTAB = 'crontab';
    const AREA_WEBAPI_REST = 'webapi_rest';
    const AREA_WEBAPI_SOAP = 'webapi_soap';

    /**
     * @deprecated
     */
    const AREA_ADMIN    = 'admin';

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
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Translator
     *
     * @var \Magento\Framework\TranslateInterface
     */
    protected $_translator;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ConfigLoaderInterface
     */
    protected $_diConfigLoader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Core design
     *
     * @var \Magento\Framework\App\DesignInterface
     */
    protected $_design;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    protected $_scopeResolver;

    /**
     * @var \Magento\Framework\View\DesignExceptions
     */
    protected $_designExceptions;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\TranslateInterface $translator
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ConfigLoaderInterface $diConfigLoader
     * @param \Magento\Framework\App\DesignInterface $design
     * @param \Magento\Framework\App\ScopeResolverInterface $scopeResolver
     * @param \Magento\Framework\View\DesignExceptions $designExceptions
     * @param string $areaCode
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\TranslateInterface $translator,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ConfigLoaderInterface $diConfigLoader,
        \Magento\Framework\App\DesignInterface $design,
        \Magento\Framework\App\ScopeResolverInterface $scopeResolver,
        \Magento\Framework\View\DesignExceptions $designExceptions,
        $areaCode
    ) {
        $this->_code = $areaCode;
        $this->_objectManager = $objectManager;
        $this->_diConfigLoader = $diConfigLoader;
        $this->_eventManager = $eventManager;
        $this->_translator = $translator;
        $this->_logger = $logger;
        $this->_design = $design;
        $this->_scopeResolver = $scopeResolver;
        $this->_designExceptions = $designExceptions;
    }

    /**
     * Load area data
     *
     * @param   string|null $part
     * @return  $this
     */
    public function load($part = null)
    {
        if ($part === null) {
            $this->_loadPart(self::PART_CONFIG)->_loadPart(self::PART_DESIGN)->_loadPart(self::PART_TRANSLATE);
        } else {
            $this->_loadPart($part);
        }
        return $this;
    }

    /**
     * Detect and apply design for the area
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     */
    public function detectDesign($request = null)
    {
        if ($this->_code == self::AREA_FRONTEND) {
            $isDesignException = $request && $this->_applyUserAgentDesignException($request);
            if (!$isDesignException) {
                $this->_design->loadChange(
                    $this->_scopeResolver->getScope()->getId()
                )->changeDesign(
                    $this->_getDesign()
                );
            }
        }
    }

    /**
     * Analyze user-agent information to override custom design settings
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    protected function _applyUserAgentDesignException($request)
    {
        try {
            $theme = $this->_designExceptions->getThemeByRequest($request);
            if (false !== $theme) {
                $this->_getDesign()->setDesignTheme($theme);
                return true;
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
        return false;
    }

    /**
     * @return \Magento\Framework\View\DesignInterface
     */
    protected function _getDesign()
    {
        return $this->_objectManager->get(\Magento\Framework\View\DesignInterface::class);
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
        \Magento\Framework\Profiler::start(
            'load_area:' . $this->_code . '.' . $part,
            ['group' => 'load_area', 'area_code' => $this->_code, 'part' => $part]
        );
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
        \Magento\Framework\Profiler::stop('load_area:' . $this->_code . '.' . $part);
        return $this;
    }

    /**
     * Load area configuration
     *
     * @return $this
     */
    protected function _initConfig()
    {
        $this->_objectManager->configure($this->_diConfigLoader->load($this->_code));
        return $this;
    }

    /**
     * Initialize translate object.
     *
     * @return $this
     */
    protected function _initTranslate()
    {
        $this->_translator->loadData(null, false);

        \Magento\Framework\Phrase::setRenderer(
            $this->_objectManager->get(\Magento\Framework\Phrase\RendererInterface::class)
        );

        return $this;
    }

    /**
     * Initialize design
     *
     * @return $this
     */
    protected function _initDesign()
    {
        $this->_getDesign()->setArea($this->_code)->setDefaultDesignTheme();
        return $this;
    }
}
