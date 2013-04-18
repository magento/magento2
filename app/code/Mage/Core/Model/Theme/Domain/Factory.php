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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme domain model class
 */
class Mage_Core_Model_Theme_Domain_Factory
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var array
     */
    protected $_types = array(
        Mage_Core_Model_Theme::TYPE_PHYSICAL => 'Mage_Core_Model_Theme_Domain_Physical',
        Mage_Core_Model_Theme::TYPE_VIRTUAL  => 'Mage_Core_Model_Theme_Domain_Virtual',
        Mage_Core_Model_Theme::TYPE_STAGING  => 'Mage_Core_Model_Theme_Domain_Staging',
    );

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new config object
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme_Domain_Physical|Mage_Core_Model_Theme_Domain_Virtual|
     * Mage_Core_Model_Theme_Domain_Staging
     * @throws Mage_Core_Exception
     */
    public function create(Mage_Core_Model_Theme $theme)
    {
        if (!isset($this->_types[$theme->getType()])) {
            throw new Mage_Core_Exception(sprintf('Invalid type of theme domain model "%s"', $theme->getType()));
        }
        $class = $this->_types[$theme->getType()];
        return $this->_objectManager->create($class, array('theme' => $theme));
    }
}
