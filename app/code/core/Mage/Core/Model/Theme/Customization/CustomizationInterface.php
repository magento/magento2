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
 * Theme customization interface
 */
interface Mage_Core_Model_Theme_Customization_CustomizationInterface
{
    /**
     * Return customization type
     */
    public function getType();

    /**
     * Setter for data for save
     *
     * @param mixed $data
     */
    public function setDataForSave($data);

    /**
     * Return collection customization form theme
     *
     * @param Mage_Core_Model_Theme_Customization_CustomizedInterface $theme
     */
    public function getCollectionByTheme(Mage_Core_Model_Theme_Customization_CustomizedInterface $theme);

    /**
     * Save data
     *
     * @param Mage_Core_Model_Theme_Customization_CustomizedInterface $theme
     */
    public function saveData(Mage_Core_Model_Theme_Customization_CustomizedInterface $theme);
}
