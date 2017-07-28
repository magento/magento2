<?php
/**
 * Application area list
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Class \Magento\Framework\App\AreaList
 *
 * @since 2.0.0
 */
class AreaList
{
    /**
     * Area configuration list
     *
     * @var array
     * @since 2.0.0
     */
    protected $_areas = [];

    /**
     * @var \Magento\Framework\App\AreaInterface[]
     * @since 2.0.0
     */
    protected $_areaInstances = [];

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_defaultAreaCode;

    /**
     * @var Area\FrontNameResolverFactory
     * @since 2.0.0
     */
    protected $_resolverFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Area\FrontNameResolverFactory $resolverFactory
     * @param array $areas
     * @param string|null $default
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Area\FrontNameResolverFactory $resolverFactory,
        array $areas = [],
        $default = null
    ) {
        $this->objectManager = $objectManager;
        $this->_resolverFactory = $resolverFactory;
        if ($areas) {
            $this->_areas = $areas;
        }
        if ($default) {
            $this->_defaultAreaCode = $default;
        }
    }

    /**
     * Retrieve area code by front name
     *
     * @param string $frontName
     * @return null|string
     * @api
     * @since 2.0.0
     */
    public function getCodeByFrontName($frontName)
    {
        foreach ($this->_areas as $areaCode => &$areaInfo) {
            if (!isset($areaInfo['frontName']) && isset($areaInfo['frontNameResolver'])) {
                $resolver = $this->_resolverFactory->create($areaInfo['frontNameResolver']);
                $areaInfo['frontName'] = $resolver->getFrontName(true);
            }
            if ($areaInfo['frontName'] == $frontName) {
                return $areaCode;
            }
        }
        return $this->_defaultAreaCode;
    }

    /**
     * Retrieve area front name by code
     *
     * @param string $areaCode
     * @return string
     * @api
     * @since 2.0.0
     */
    public function getFrontName($areaCode)
    {
        return isset($this->_areas[$areaCode]['frontName']) ? $this->_areas[$areaCode]['frontName'] : null;
    }

    /**
     * Retrieve area codes
     *
     * @return string[]
     * @api
     * @since 2.0.0
     */
    public function getCodes()
    {
        return array_keys($this->_areas);
    }

    /**
     * Retrieve default area router id
     *
     * @param string $areaCode
     * @return string
     * @api
     * @since 2.0.0
     */
    public function getDefaultRouter($areaCode)
    {
        return isset($this->_areas[$areaCode]['router']) ? $this->_areas[$areaCode]['router'] : null;
    }

    /**
     * Retrieve application area
     *
     * @param   string $code
     * @return  \Magento\Framework\App\Area
     * @since 2.0.0
     */
    public function getArea($code)
    {
        if (!isset($this->_areaInstances[$code])) {
            $this->_areaInstances[$code] = $this->objectManager->create(
                \Magento\Framework\App\AreaInterface::class,
                ['areaCode' => $code]
            );
        }
        return $this->_areaInstances[$code];
    }
}
