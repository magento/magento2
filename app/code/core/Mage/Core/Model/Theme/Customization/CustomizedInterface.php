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
 * Theme customized interface
 */
interface Mage_Core_Model_Theme_Customization_CustomizedInterface
{
    /**
     * Setter customization to customized theme
     *
     * @var Mage_Core_Model_Theme_Customization_CustomizationInterface $customization
     */
    public function setCustomization(Mage_Core_Model_Theme_Customization_CustomizationInterface $customization);

    /**
     * Return theme customization collection by type
     *
     * @param string $type
     */
    public function getCustomizationData($type);

    /**
     * Save theme customizations
     */
    public function saveThemeCustomization();

    /**
     * Check whether present customization objects
     */
    public function isCustomized();

    /**
     * Return path to customized theme files
     *
     * @return string|null
     */
    public function getCustomizationPath();
}
