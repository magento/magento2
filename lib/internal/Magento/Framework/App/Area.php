<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\ObjectManager\ConfigLoaderInterface;

/**
 * Application area model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 */
class Area implements \Magento\Framework\App\AreaInterface
{
    const AREA_GLOBAL = 'global';
    const AREA_FRONTEND = 'frontend';
    const AREA_ADMINHTML = 'adminhtml';
    const AREA_DOC = 'doc';
    const AREA_CRONTAB = 'crontab';
    const AREA_WEBAPI_REST = 'webapi_rest';
    const AREA_WEBAPI_SOAP = 'webapi_soap';
    const AREA_GRAPHQL = 'graphql';

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
    private $loadedParts;

    /**
     * Area code
     *
     * @var string
     */
    private $code;

    /**
     * Event Manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * Translator
     *
     * @var \Magento\Framework\TranslateInterface
     */
    private $translator;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ConfigLoaderInterface
     */
    private $diConfigLoader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Core design
     *
     * @var \Magento\Framework\App\DesignInterface
     */
    private $design;

    /**
     * @var \Magento\Framework\App\ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var \Magento\Framework\View\DesignExceptions
     */
    private $designExceptions;

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
        $this->code = $areaCode;
        $this->objectManager = $objectManager;
        $this->diConfigLoader = $diConfigLoader;
        $this->eventManager = $eventManager;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->design = $design;
        $this->scopeResolver = $scopeResolver;
        $this->designExceptions = $designExceptions;
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
            $this->loadPart(self::PART_CONFIG)->loadPart(self::PART_DESIGN)->loadPart(self::PART_TRANSLATE);
        } else {
            $this->loadPart($part);
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
        if ($this->code == self::AREA_FRONTEND) {
            $isDesignException = $request && $this->applyUserAgentDesignException($request);
            if (!$isDesignException) {
                $this->design->loadChange(
                    $this->scopeResolver->getScope()->getId()
                )->changeDesign(
                    $this->getDesign()
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
    private function applyUserAgentDesignException($request)
    {
        try {
            $theme = $this->designExceptions->getThemeByRequest($request);
            if (false !== $theme) {
                $this->getDesign()->setDesignTheme($theme);
                return true;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return false;
    }

    /**
     * @return \Magento\Framework\View\DesignInterface
     */
    private function getDesign()
    {
        return $this->objectManager->get(\Magento\Framework\View\DesignInterface::class);
    }

    /**
     * Loading part of area
     *
     * @param   string $part
     * @return  $this
     */
    private function loadPart($part)
    {
        if (isset($this->loadedParts[$part])) {
            return $this;
        }
        \Magento\Framework\Profiler::start(
            'load_area:' . $this->code . '.' . $part,
            ['group' => 'load_area', 'area_code' => $this->code, 'part' => $part]
        );
        switch ($part) {
            case self::PART_CONFIG:
                $this->initConfig();
                break;
            case self::PART_TRANSLATE:
                $this->initTranslate();
                break;
            case self::PART_DESIGN:
                $this->initDesign();
                break;
        }
        $this->loadedParts[$part] = true;
        \Magento\Framework\Profiler::stop('load_area:' . $this->code . '.' . $part);
        return $this;
    }

    /**
     * Load area configuration
     *
     * @return $this
     */
    private function initConfig()
    {
        $this->objectManager->configure($this->diConfigLoader->load($this->code));
        return $this;
    }

    /**
     * Initialize translate object.
     *
     * @return $this
     */
    private function initTranslate()
    {
        $this->translator->loadData(null, false);

        \Magento\Framework\Phrase::setRenderer(
            $this->objectManager->get(\Magento\Framework\Phrase\RendererInterface::class)
        );

        return $this;
    }

    /**
     * Initialize design
     *
     * @return $this
     */
    private function initDesign()
    {
        $this->getDesign()->setArea($this->code)->setDefaultDesignTheme();
        return $this;
    }
}
