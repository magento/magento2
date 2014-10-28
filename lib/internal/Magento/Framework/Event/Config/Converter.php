<?php
/**
 * Converter of event observers configuration from \DOMDocument to tree array
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
namespace Magento\Framework\Event\Config;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $output = array();
        /** @var \DOMNodeList $events */
        $events = $source->getElementsByTagName('event');
        /** @var \DOMNode $eventConfig */
        foreach ($events as $eventConfig) {
            $eventName = $eventConfig->attributes->getNamedItem('name')->nodeValue;
            $eventObservers = array();
            /** @var \DOMNode $observerConfig */
            foreach ($eventConfig->childNodes as $observerConfig) {
                if ($observerConfig->nodeName != 'observer' || $observerConfig->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }
                $observerNameNode = $observerConfig->attributes->getNamedItem('name');
                if (!$observerNameNode) {
                    throw new \InvalidArgumentException('Attribute name is missed');
                }
                $config = $this->_convertObserverConfig($observerConfig);
                $config['name'] = $observerNameNode->nodeValue;
                $eventObservers[$observerNameNode->nodeValue] = $config;
            }
            $output[$eventName] = $eventObservers;
        }
        return $output;
    }

    /**
     * Convert observer configuration
     *
     * @param \DOMNode $observerConfig
     * @return array
     */
    public function _convertObserverConfig($observerConfig)
    {
        $output = array();
        /** Parse instance configuration */
        $instanceAttribute = $observerConfig->attributes->getNamedItem('instance');
        if ($instanceAttribute) {
            $output['instance'] = $instanceAttribute->nodeValue;
        }

        /** Parse instance method configuration */
        $methodAttribute = $observerConfig->attributes->getNamedItem('method');
        if ($methodAttribute) {
            $output['method'] = $methodAttribute->nodeValue;
        }

        /** Parse disabled/enabled configuration */
        $disabledAttribute = $observerConfig->attributes->getNamedItem('disabled');
        if ($disabledAttribute && $disabledAttribute->nodeValue == 'true') {
            $output['disabled'] = true;
        }

        /** Parse shareability configuration */
        $shredAttribute = $observerConfig->attributes->getNamedItem('shared');
        if ($shredAttribute && $shredAttribute->nodeValue == 'false') {
            $output['shared'] = false;
        }

        return $output;
    }
}
