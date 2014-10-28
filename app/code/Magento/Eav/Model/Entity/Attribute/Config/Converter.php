<?php
/**
 * Attributes configuration converter
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Eav\Model\Entity\Attribute\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert config
     *
     * @param mixed $source
     * @return array
     */
    public function convert($source)
    {
        $output = array();

        /** @var \DOMNodeList $entities */
        $entities = $source->getElementsByTagName('entity');

        /** @var DOMNode $entity */
        foreach ($entities as $entity) {
            $entityConfig = array();
            $attributes = array();

            /** @var DOMNode $entityAttribute */
            foreach ($entity->getElementsByTagName('attribute') as $entityAttribute) {
                $attributeFields = array();
                foreach ($entityAttribute->getElementsByTagName('field') as $fieldData) {
                    $locked = $fieldData->attributes->getNamedItem('locked')->nodeValue == "true" ? true : false;
                    $attributeFields[$fieldData->attributes->getNamedItem(
                        'code'
                    )->nodeValue] = array(
                        'code' => $fieldData->attributes->getNamedItem('code')->nodeValue,
                        'locked' => $locked
                    );
                }
                $attributes[$entityAttribute->attributes->getNamedItem('code')->nodeValue] = $attributeFields;
            }
            $entityConfig['attributes'] = $attributes;
            $output[$entity->attributes->getNamedItem('type')->nodeValue] = $entityConfig;
        }

        return $output;
    }
}
