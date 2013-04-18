<?php
/**
 * Configuration integrity tests
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

class Integrity_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * Validate cache types in the config
     *
     * @dataProvider cacheTypesDataProvider
     */
    public function testCacheTypes(SimpleXMLElement $node)
    {
        $requiredNodes = array('label', 'description', 'class');
        /** @var $node SimpleXMLElement */
        foreach ($requiredNodes as $requiredNode) {
            $this->assertObjectHasAttribute($requiredNode, $node,
                "Required '$requiredNode' node is not specified for '" . $node->getName() . "' cache type");
        }

        $this->assertTrue(class_exists($node->class),
            "Class '{$node->class}', specified for '" . $node->getName() . "' cache type, doesn't exist");
        $interfaces = class_implements((string) $node->class);
        $this->assertContains('Magento_Cache_FrontendInterface', $interfaces,
            "Class '{$node->class}', specified for '" . $node->getName()
                . "' cache type, must implement 'Magento_Cache_FrontendInterface'"
        );
    }

    /**
     * @return array
     */
    public function cacheTypesDataProvider()
    {
        $config = Mage::app()->getConfig();
        $nodes = array();
        /** @var $node SimpleXMLElement */
        foreach ($config->getXpath('/config/global/cache/types/*') as $node) {
            $nodes[$node->getName()] = array($node);
        }

        return $nodes;
    }
}
