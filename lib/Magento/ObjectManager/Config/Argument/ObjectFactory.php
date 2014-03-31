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
namespace Magento\ObjectManager\Config\Argument;

use Magento\ObjectManager;
use Magento\ObjectManager\Config;

/**
 * Factory that creates an instance by a type name taking into account whether it's shared or not
 */
class ObjectFactory
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param Config $config
     * @param ObjectManager $objectManager
     */
    public function __construct(Config $config, ObjectManager $objectManager = null)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * Assign object manager instance
     *
     * @param ObjectManager $objectManager
     * @return void
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve instance of the object manager
     *
     * @return ObjectManager
     * @throws \LogicException
     */
    protected function getObjectManager()
    {
        if (!$this->objectManager) {
            throw new \LogicException('Object manager has not been assigned yet.');
        }
        return $this->objectManager;
    }

    /**
     * Return new or shared instance of a given type
     *
     * @param string $type
     * @param bool|null $isShared NULL - use the sharing configuration
     * @return object
     */
    public function create($type, $isShared = null)
    {
        $objectManager = $this->getObjectManager();
        $isShared = isset($isShared) ? $isShared : $this->config->isShared($type);
        $result = $isShared ? $objectManager->get($type) : $objectManager->create($type);
        return $result;
    }
}
