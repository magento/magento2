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

namespace Magento\Core\Model\Layout;

class Factory
{
    /**
     * Default layout class name
     */
    const CLASS_NAME = 'Magento\Core\Model\Layout';

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
     * @param array $arguments
     * @param string $className
     * @return \Magento\Core\Model\Layout
     */
    public function createLayout(array $arguments = array(), $className = self::CLASS_NAME)
    {
        $configuration = array(
            $className => array(
                'parameters' => $arguments
            )
        );
        if ($className != self::CLASS_NAME) {
            $configuration['preferences'] = array(
                self::CLASS_NAME => $className,
            );
        }
        $this->_objectManager->configure($configuration);
        return $this->_objectManager->get(self::CLASS_NAME);
    }
}
