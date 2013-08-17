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

/**
 * Translate factory
 */
class Mage_Core_Model_Translate_Factory
{
    /**
     * Default translate inline class name
     */
    const DEFAULT_CLASS_NAME = 'Mage_Core_Model_Translate_Inline';

    /**
     * Object Manager
     *
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Object constructor
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Return instance of inline translate object based on passed in class name.
     *
     * @param array $data
     * @param string $className
     * @return Mage_Core_Model_Translate_InlineInterface
     */
    public function create(array $data = null, $className = null)
    {
        if ($className === null) {
            $className = self::DEFAULT_CLASS_NAME;
        }
        return $this->_objectManager->get($className, $data);
    }
}
