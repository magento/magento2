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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * Specific renderer list
     *
     * @var array
     */
    protected $_specificRenderer = array(
        self::BACKGROUND_IMAGE => 'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\Renderer\BackgroundImage'
    );

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
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
