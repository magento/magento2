<?php
/**
 * Locale hierarchy configuration converter
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Locale\Hierarchy\Config;

class Converter implements \Magento\Config\ConverterInterface
{
    /**
     * Compose locale inheritance hierarchy based on given config
     *
     * @param array $localeConfig assoc array where key is a code of locale and value is a code of its parent locale
     * @return array
     */
    protected function _composeLocaleHierarchy($localeConfig)
    {
        $localeHierarchy = array();
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
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = array();
        /** @var \DOMNodeList $locales */
        $locales = $source->getElementsByTagName('locale');
        /** @var \DOMNode $locale */
        foreach ($locales as $locale) {
            $parent = $locale->attributes->getNamedItem('parent');
            if ($parent) {
                $output[$locale->attributes->getNamedItem('code')->nodeValue] = $parent->nodeValue;
            }

        }
        return $this->_composeLocaleHierarchy($output);
    }
}
