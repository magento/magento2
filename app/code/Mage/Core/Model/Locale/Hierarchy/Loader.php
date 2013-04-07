<?php
/**
 * Locale inheritance hierarchy loader
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Locale_Hierarchy_Loader
{
    const XML_PATH_LOCALE_INHERITANCE = 'global/locale/inheritance';

    /**
     * @var Mage_Core_Model_Config_Locales
     */
    protected $_config;

    /**
     * Locales configuration model
     *
     * @param Mage_Core_Model_Config_Locales $config
     */
    public function __construct(Mage_Core_Model_Config_Locales $config)
    {
        $this->_config = $config;
    }

    /**
     * Compose locale inheritance hierarchy based on given config
     *
     * @param array|string $localeConfig assoc array where key is a code of locale and value is a code of its parent locale
     * @return array
     */
    protected function _composeLocaleHierarchy($localeConfig)
    {
        $localeHierarchy = array();
        if (!is_array($localeConfig)) {
            return $localeHierarchy;
        }

        foreach ($localeConfig as $locale => $localeParent) {
            $localeParents = array($localeParent);
            while (isset($localeConfig[$localeParent]) && !in_array($localeConfig[$localeParent], $localeParents)
                && $locale != $localeConfig[$localeParent]
            ) {
                // inheritance chain starts with the deepest parent
                array_unshift($localeParents, $localeConfig[$localeParent]);
                $localeParent = $localeConfig[$localeParent];
            }
            // store hierarchy for current locale
            $localeHierarchy[$locale] = $localeParents;
        }
        return $localeHierarchy;
    }

    /**
     * Load locales inheritance hierarchy
     *
     * @return array
     */
    public function load()
    {
        $localeHierarchy = array();
        $inheritanceNode = $this->_config->getNode(self::XML_PATH_LOCALE_INHERITANCE);
        if ($inheritanceNode instanceof Varien_Simplexml_Element) {
            $localeHierarchy = $this->_composeLocaleHierarchy(
                $inheritanceNode->asCanonicalArray()
            );
        }
        return $localeHierarchy;
    }
}
