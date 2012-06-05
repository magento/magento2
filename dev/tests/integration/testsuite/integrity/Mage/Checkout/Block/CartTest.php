<?php
/**
 * Integrity test for template setters in Mage_Checkout_Block_CartTest
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
 * @category Mage
 * @package Mage_Checkout
 * @subpackage integration_tests
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Integrity_Mage_Checkout_Block_CartTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $layoutFile
     * @dataProvider layoutFilesDataProvider
     */
    public function testCustomTemplateSetters($layoutFile)
    {
        $params = array();
        if (preg_match('/app\/design\/frontend\/(.+?)\/(.+?)\//', $layoutFile, $matches)) {
            $params = array('_package' => $matches[1], '_theme' => $matches[2]);
        }

        $xml = simplexml_load_file($layoutFile);
        $nodes = $xml->xpath('//block/action[@method="setCartTemplate" or @method="setEmptyTemplate"]') ?: array();
        /** @var $node SimpleXMLElement */
        foreach ($nodes as $node) {
            $template = (array)$node->children();
            $template = array_shift($template);
            $blockNode = $node->xpath('..');
            $blockNode = $blockNode[0];
            preg_match('/^(.+?_.+?)_/', $blockNode['type'], $matches);
            $params['_module'] = $matches[1];
            $this->assertFileExists(Mage::getDesign()->getFilename($template, $params));
        }
    }

    /**
     * @return array
     */
    public function layoutFilesDataProvider()
    {
        return Utility_Files::init()->getLayoutFiles(array('area' => 'frontend'));
    }
}
