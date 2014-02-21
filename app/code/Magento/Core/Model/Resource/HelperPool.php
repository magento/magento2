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

/**
 * Resource helper pool
 */
namespace Magento\Core\Model\Resource;

class HelperPool
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_resourceHelpers = array();

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(
        \Magento\ObjectManager $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get resource helper singleton
     *
     * @param string $moduleName
     * @throws \InvalidArgumentException
     * @return \Magento\Core\Model\Resource\Helper\AbstractHelper
     */
    public function get($moduleName)
    {
        $module = str_replace('_', \Magento\Autoload\IncludePath::NS_SEPARATOR, $moduleName);
        $helperClassName = $module . '\Model\Resource\Helper';
        $connection = strtolower($moduleName);
        if (substr($moduleName, 0, 8) == 'Magento_') {
            $connection = substr($connection, 8);
        }

        if (!isset($this->_resourceHelpers[$connection])) {
            $helper = $this->_objectManager->create($helperClassName, array('modulePrefix' => $connection));
            if (false === ($helper instanceof \Magento\Core\Model\Resource\Helper\AbstractHelper)) {
                throw new \InvalidArgumentException(
                    $helperClassName . ' doesn\'t extend \Magento\Core\Model\Resource\Helper\AbstractHelper'
                );
            }
            $this->_resourceHelpers[$connection] = $helper;
        }

        return $this->_resourceHelpers[$connection];
    }
}
