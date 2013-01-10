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
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Layout_Factory
{
    /**
     * Default layout class name
     */
    const CLASS_NAME = 'Mage_Core_Model_Layout';

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param array $arguments
     * @param string $className
     * @return Mage_Core_Model_Layout
     */
    public function createLayout(array $arguments = array(), $className = self::CLASS_NAME)
    {
        // because layout singleton was used everywhere in magento code, in observers, models, blocks, etc.
        // the only way how we can replace default layout object with custom one is to save instance of custom layout
        // to instance manager storage using default layout class name as alias
        $createLayout = true;
        if (isset($arguments['area'])) {
            if ($this->_objectManager->hasSharedInstance(self::CLASS_NAME)) {
                /** @var $layout Mage_Core_Model_Layout */
                $layout = $this->_objectManager->get(self::CLASS_NAME);
                if ($arguments['area'] != $layout->getArea()) {
                    $this->_objectManager->removeSharedInstance(self::CLASS_NAME);
                } else {
                    $createLayout = false;
                }
            }
        }
        if ($createLayout) {
            $layout = $this->_objectManager->create($className, $arguments, false);
            $this->_objectManager->addSharedInstance($layout, self::CLASS_NAME);
        }

        return $this->_objectManager->get(self::CLASS_NAME);
    }
}
