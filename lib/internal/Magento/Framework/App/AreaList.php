<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Lists router area codes & processes resolves FrontEndNames to area codes
 *
 * @api
 */
class AreaList implements ResetAfterRequestInterface
{
    /**
     * @var array
     */
    protected $_areas = [];

    /**
     * @var \Magento\Framework\App\AreaInterface[]
     */
    protected $_areaInstances = [];

    /**
     * @var string
     */
    protected $_defaultAreaCode;

    /**
     * @var Area\FrontNameResolverFactory
     */
    protected $_resolverFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Area\FrontNameResolverFactory $resolverFactory
     * @param array $areas
     * @param string|null $default
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
     */
    public function getCodeByFrontName($frontName)
    {
        foreach ($this->_areas as $areaCode => &$areaInfo) {
            if (!isset($areaInfo['frontName']) && isset($areaInfo['frontNameResolver'])) {
                $resolver = $this->_resolverFactory->create($areaInfo['frontNameResolver']);
                $areaInfo['frontName'] = $resolver->getFrontName(true);
            }
            if (isset($areaInfo['frontName']) && $areaInfo['frontName'] === $frontName) {
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
     */
    public function getFrontName($areaCode)
    {
        return $this->_areas[$areaCode]['frontName'] ?? null;
    }

    /**
     * Retrieve area codes
     *
     * @return string[]
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
     */
    public function getDefaultRouter($areaCode)
    {
        return $this->_areas[$areaCode]['router'] ?? null;
    }

    /**
     * Retrieve application area
     *
     * @param   string $code
     * @return  \Magento\Framework\App\Area
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

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_areaInstances = [];
    }
}
