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

namespace Magento\Framework\Serialization;

use Magento\Framework\ObjectManager;

/**
 * Factory used to construct Data Builder based on interface name
 */
class DataBuilderFactory
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Returns a builder for a given class name.
     *
     * @param string $className
     * @return \Magento\Framework\Api\BuilderInterface Builder Instance
     */
    public function getDataBuilder($className)
    {
        $interfaceSuffix = 'Interface';
        if (substr($className, -strlen($interfaceSuffix)) === $interfaceSuffix) {
            /** If class name ends with Interface, replace it with Data suffix */
            $builderClassName = substr($className, 0, -strlen($interfaceSuffix)) . 'Data';
        } else {
            $builderClassName = $className;
        }
        $builderClassName .= 'Builder';
        return $this->objectManager->create($builderClassName);
    }
}
