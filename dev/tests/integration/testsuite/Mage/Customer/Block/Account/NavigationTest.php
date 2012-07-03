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
 * @package     Mage_Customer
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Customer_Block_Account_NavigationTest extends PHPUnit_Framework_TestCase
{
    public function testAddRemoveLink()
    {
        $block = new Mage_Customer_Block_Account_Navigation;
        $this->assertSame(array(), $block->getLinks());
        $this->assertSame($block, $block->addLink('Name', 'some/path/index', 'Label', array('parameter' => 'value')));
        $links = $block->getLinks();
        $this->assertArrayHasKey('Name', $links);
        $this->assertInstanceOf('Varien_Object', $links['Name']);
        $this->assertSame(array(
                'name' => 'Name', 'path' => 'some/path/index', 'label' => 'Label',
                'url' => 'http://localhost/index.php/some/path/index/parameter/value/'
            ), $links['Name']->getData()
        );
        $block->removeLink('nonexistent');
        $this->assertSame($links, $block->getLinks());
        $block->removeLink('Name');
        $this->assertSame(array(), $block->getLinks());
    }
}
