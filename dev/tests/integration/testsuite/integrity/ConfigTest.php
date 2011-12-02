<?php
/**
 * Integrity test for configuration (config.xml)
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
 * @package     integration
 * @subpackage  integrity
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Integrity_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * The node 'config/global/fieldsets' had been relocated from config.xml to fieldset.xml
     *
     * @param string $path
     * @dataProvider fieldsetsRemovalDataProvider
     */
    public function testFieldsetsRemoval($path)
    {
        $this->assertFalse(Mage::getConfig()->getNode($path));
        $config = new Mage_Core_Model_Config_Fieldset;
        $this->assertInstanceOf('Mage_Core_Model_Config_Element', $config->getNode($path));
    }

    public function fieldsetsRemovalDataProvider()
    {
        return array(array('global/fieldsets'), array('admin/fieldsets'));
    }

    /**
     * The "deprecated node" feature had been removed
     */
    public function testDeprecatedNodeRemoval()
    {
        $this->assertSame(array(), Mage::getConfig()->getNode()->xpath('/config/global/models/*/deprecatedNode'));
    }

    /**
     * Verification that there no table definition in configuration
     */
    public function testTableDefinitionRemoval()
    {
        $this->assertSame(array(), Mage::getConfig()->getNode()->xpath('/config/global/models/*/entities/*/table'));
    }

    /**
     * @dataProvider classPrefixRemovalDataProvider
     */
    public function testClassPrefixRemoval($classType)
    {
        $this->assertSame(array(), Mage::getConfig()->getNode()->xpath("/config/global/{$classType}s/*/class"));
    }

    public function classPrefixRemovalDataProvider()
    {
        return array(
            array('model'),
            array('helper'),
            array('block'),
        );
    }

    public function testResourceModelMappingRemoval()
    {
        $this->assertSame(array(), Mage::getConfig()->getNode()->xpath('/config/global/models/*/resourceModel'));
    }
}
