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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper factory model. Used to get helper objects
 */
namespace Magento\Core\Model\Factory;

class Helper
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get helper singleton
     *
     * @param string $className
     * @param array $arguments
     * @return \Magento\Core\Helper\AbstractHelper
     * @throws \LogicException
     */
    public function get($className, array $arguments = array())
    {
        $className = str_replace('_', '\\', $className);
        /* Default helper class for a module */
        if (strpos($className, '\Helper\\') === false) {
            $className .= '\Helper\Data';
        }

        $helper = $this->_objectManager->get($className, $arguments);

        if (false === ($helper instanceof \Magento\Core\Helper\AbstractHelper)) {
            throw new \LogicException(
                $className . ' doesn\'t extends Magento\Core\Helper\AbstractHelper'
            );
        }

        return $helper;
    }
}
