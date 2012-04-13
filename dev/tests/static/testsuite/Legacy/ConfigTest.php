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
 * @category    tests
 * @package     static
 * @subpackage  Legacy
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tests for obsolete and removed config nodes
 */
class Legacy_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param string $file
     * @dataProvider configFileDataProvider
     */
    public function testConfigFile($file)
    {
        $obsoleteNodes = array(
            '/config/global/fieldsets'                 => '',
            '/config/admin/fieldsets'                  => '',
            '/config/global/models/*/deprecatedNode'   => '',
            '/config/global/models/*/entities/*/table' => '',
            '/config/global/models/*/class'            => '',
            '/config/global/helpers/*/class'           => '',
            '/config/global/blocks/*/class'            => '',
            '/config/global/models/*/resourceModel'    => '',
            '/config/adminhtml/menu'                   => 'Move them to adminhtml.xml.',
            '/config/adminhtml/acl'                    => 'Move them to adminhtml.xml.',
            '/config/*/events/core_block_abstract_to_html_after' =>
                'Event has been replaced with "core_layout_render_element"',
            '/config/*/events/catalog_controller_product_delete' => '',
        );
        $xml = simplexml_load_file($file);
        foreach ($obsoleteNodes as $xpath => $suggestion) {
            $this->assertEmpty(
                $xml->xpath($xpath),
                "Nodes identified by XPath '$xpath' are obsolete. $suggestion"
            );
        }
    }

    /**
     * @return array
     */
    public function configFileDataProvider()
    {
        return Utility_Files::init()->getConfigFiles('config.xml');
    }
}
