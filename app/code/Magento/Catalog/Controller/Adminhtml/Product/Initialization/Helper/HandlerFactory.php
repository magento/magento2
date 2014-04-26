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
namespace Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

class HandlerFactory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create handler instance
     *
     * @param string $instance
     * @param array $arguments
     * @return object
     * @throws \InvalidArgumentException
     */
    public function create($instance, array $arguments = array())
    {

        if (!is_subclass_of(
            $instance,
            '\Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface'
        )
        ) {
            throw new \InvalidArgumentException(
                $instance .
                ' does not implement ' .
                'Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\HandlerInterface'
            );
        }

        return $this->objectManager->create($instance, $arguments);
    }
}
