<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer;

/**
 * Quick style renderer factory
 */
class Factory
{
    /**
     * Background image attribute
     */
    const BACKGROUND_IMAGE = 'background-image';

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Specific renderer list
     *
     * @var array
     */
    protected $_specificRenderer = [
        self::BACKGROUND_IMAGE => 'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\BackgroundImage',
    ];

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new instance
     *
     * @param string $attribute
     * @return \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\AbstractRenderer
     */
    public function get($attribute)
    {
        $renderer = array_key_exists(
            $attribute,
            $this->_specificRenderer
        ) ? $this->_specificRenderer[$attribute] : 'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\DefaultRenderer';

        return $this->_objectManager->create($renderer);
    }
}
