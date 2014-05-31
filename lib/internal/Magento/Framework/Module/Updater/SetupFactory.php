<?php
/**
 * Module setup factory. Creates setups used during application install/upgrade.
 *
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
namespace Magento\Framework\Module\Updater;

use Magento\Framework\ObjectManager;

class SetupFactory
{
    const INSTANCE_TYPE = 'Magento\Framework\Module\Updater\SetupInterface';

    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_resourceTypes;

    /**
     * @param ObjectManager $objectManager
     * @param array $resourceTypes
     */
    public function __construct(ObjectManager $objectManager, array $resourceTypes)
    {
        $this->_objectManager = $objectManager;
        $this->_resourceTypes = $resourceTypes;
    }

    /**
     * @param string $resourceName
     * @param string $moduleName
     * @return SetupInterface
     * @throws \LogicException
     */
    public function create($resourceName, $moduleName)
    {
        $className = isset(
            $this->_resourceTypes[$resourceName]
        ) ? $this->_resourceTypes[$resourceName] : 'Magento\Framework\Module\Updater\SetupInterface';

        if (false == is_subclass_of($className, self::INSTANCE_TYPE) && $className !== self::INSTANCE_TYPE) {
            throw new \LogicException($className . ' is not a \Magento\Framework\Module\Updater\SetupInterface');
        }

        return $this->_objectManager->create(
            $className,
            array('resourceName' => $resourceName, 'moduleName' => $moduleName)
        );
    }
}
