<?php
/**
 * Converter of customer address format configuration from \DOMDocument to array
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model\Address\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert customer address format configuration from dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = array();
        /** @var \DOMNodeList $formats */
        $formats = $source->getElementsByTagName('format');
        /** @var \DOMNode $formatConfig */
        foreach ($formats as $formatConfig) {
            $formatCode = $formatConfig->attributes->getNamedItem('code')->nodeValue;
            $output[$formatCode] = array();
            for ($attributeIndex = 0; $attributeIndex < $formatConfig->attributes->length; $attributeIndex++) {
                $output[$formatCode][$formatConfig->attributes->item(
                    $attributeIndex
                )->nodeName] = $formatConfig->attributes->item(
                    $attributeIndex
                )->nodeValue;
            }
        }
        return $output;
    }
}
