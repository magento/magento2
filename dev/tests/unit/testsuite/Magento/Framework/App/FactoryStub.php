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
namespace Magento\Framework\App;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FactoryStub implements \Magento\Framework\ObjectManager\Factory
{
    /**
     * @param \Magento\Framework\ObjectManager\Config $config
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\ObjectManager\Definition $definitions
     * @param array $globalArguments
     * @throws \BadMethodCallException
     */
    public function __construct($config, $objectManager = null, $definitions = null, $globalArguments = array())
    {
        throw new \BadMethodCallException(__METHOD__);
    }

    /**
     * Create instance with call time arguments
     *
     * @param string $requestedType
     * @param array $arguments
     * @return object
     * @throws \BadMethodCallException
     */
    public function create($requestedType, array $arguments = array())
    {
        throw new \BadMethodCallException(__METHOD__);
    }
}
