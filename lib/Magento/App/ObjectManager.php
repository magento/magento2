<?php
/**
 * Magento application object manager. Configures and application application
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
namespace Magento\App;
use Magento\ObjectManager\Factory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ObjectManager extends \Magento\ObjectManager\ObjectManager
{
    /**
     * @var \Magento\App\ObjectManager
     */
    protected static $_instance;

    /**
     * @var \Magento\ObjectManager\Relations
     */
    protected $_compiledRelations;

    /**
     * Retrieve object manager
     *
     * TODO: Temporary solution for serialization, should be removed when Serialization problem is resolved
     *
     * @deprecated
     * @return \Magento\ObjectManager
     * @throws \RuntimeException
     */
    public static function getInstance()
    {
        if (!self::$_instance instanceof \Magento\ObjectManager) {
            throw new \RuntimeException('ObjectManager isn\'t initialized');
        }
        return self::$_instance;
    }

    /**
     * Set object manager instance
     *
     * @param \Magento\ObjectManager $objectManager
     * @throws \LogicException
     */
    public static function setInstance(\Magento\ObjectManager $objectManager)
    {
        self::$_instance = $objectManager;
    }

    /**
     * @param Factory $factory
     * @param \Magento\ObjectManager\Config $config
     * @param array $sharedInstances
     */
    public function __construct(
        Factory $factory = null, \Magento\ObjectManager\Config $config = null, array $sharedInstances = array()
    ) {
        parent::__construct($factory, $config, $sharedInstances);
        self::$_instance = $this;
    }
}
