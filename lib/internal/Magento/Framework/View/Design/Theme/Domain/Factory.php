<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Domain;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme domain model class factory
 */
class Factory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Types
     *
     * @var array
     */
    protected $_types = [
        ThemeInterface::TYPE_PHYSICAL => 'Magento\Framework\View\Design\Theme\Domain\PhysicalInterface',
        ThemeInterface::TYPE_VIRTUAL => 'Magento\Framework\View\Design\Theme\Domain\VirtualInterface',
        ThemeInterface::TYPE_STAGING => 'Magento\Framework\View\Design\Theme\Domain\StagingInterface',
    ];

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new config object
     *
     * @param ThemeInterface $theme
     * @return mixed
     * @throws \Magento\Framework\Exception
     */
    public function create(ThemeInterface $theme)
    {
        if (!isset($this->_types[$theme->getType()])) {
            throw new \Magento\Framework\Exception(
                sprintf('Invalid type of theme domain model "%s"', $theme->getType())
            );
        }
        $class = $this->_types[$theme->getType()];
        return $this->_objectManager->create($class, ['theme' => $theme]);
    }
}
