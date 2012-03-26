<?php
/**
 * Test VS backwards-incompatible changes in widget.xml
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
 * @category    tests
 * @package     static
 * @subpackage  Legacy
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * A test for backwards-incompatible change in widget.xml structure
 */
class Legacy_Mage_Widget_XmlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @dataProvider widgetXmlFilesDataProvider
     */
    public function testClassFactoryNames($file)
    {
        $xml = simplexml_load_file($file);
        $nodes = $xml->xpath('/widgets/*[@type]') ?: array();
        /** @var SimpleXMLElement $node */
        foreach ($nodes as $node) {
            $type = (string)$node['type'];
            $this->assertNotRegExp('/\//', $type, "Factory name detected: {$type}.");
        }
    }

    /**
     * @param string $file
     * @dataProvider widgetXmlFilesDataProvider
     */
    public function testBlocksIntoContainers($file)
    {
        $xml = simplexml_load_file($file);
        $this->assertSame(array(), $xml->xpath('/widgets/*/supported_blocks'),
            'Obsolete node: <supported_blocks>. To be replaced with <supported_containers>'
        );
        $this->assertSame(array(), $xml->xpath('/widgets/*/*/*/block_name'),
            'Obsolete node: <block_name>. To be replaced with <container_name>'
        );
    }

    /**
     * @return array
     */
    public function widgetXmlFilesDataProvider()
    {
        return Utility_Files::init()->getConfigFiles('widget.xml');
    }
}
