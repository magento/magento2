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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\ObjectManager\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetArgumentsEmpty()
    {
        $config = new Config();
        $this->assertSame(array(), $config->getArguments('An invalid type'));
    }

    public function testExtendMergeConfiguration()
    {
        $this->_assertFooTypeArguments(new Config());
    }

    /**
     * A primitive fixture for testing merging arguments
     *
     * @param Config $config
     */
    private function _assertFooTypeArguments(Config $config)
    {
        $expected = array('argName' => 'argValue');
        $fixture = array('FooType' => array('arguments' => $expected));
        $config->extend($fixture);
        $this->assertEquals($expected, $config->getArguments('FooType'));
    }

    public function testExtendWithCacheMock()
    {
        $definitions = $this->getMockForAbstractClass('\Magento\Framework\ObjectManager\Definition');
        $definitions->expects($this->once())->method('getClasses')->will($this->returnValue(array('FooType')));

        $cache = $this->getMockForAbstractClass('\Magento\Framework\ObjectManager\ConfigCache');
        $cache->expects($this->once())->method('get')->will($this->returnValue(false));

        $config = new Config(null, $definitions);
        $config->setCache($cache);

        $this->_assertFooTypeArguments($config);
    }

    public function testGetPreferenceTrimsFirstSlash()
    {
        $config = new Config();
        $this->assertEquals('Some\Class\Name', $config->getPreference('\Some\Class\Name'));
    }

    public function testExtendIgnoresFirstSlashesOnPreferences()
    {
        $config = new Config();
        $config->extend(array('preferences' => array('\Some\Interface' => '\Some\Class')));
        $this->assertEquals('Some\Class', $config->getPreference('Some\Interface'));
        $this->assertEquals('Some\Class', $config->getPreference('\Some\Interface'));
    }

    public function testExtendIgnoresFirstShashesOnVirtualTypes()
    {
        $config = new Config();
        $config->extend(array('\SomeVirtualType' => array('type' => '\Some\Class')));
        $this->assertEquals('Some\Class', $config->getInstanceType('SomeVirtualType'));
    }

    public function testExtendIgnoresFirstShashes()
    {
        $config = new Config();
        $config->extend(array('\Some\Class' => array('arguments' => array('someArgument'))));
        $this->assertEquals(array('someArgument'), $config->getArguments('Some\Class'));
    }

    public function testExtendIgnoresFirstShashesForSharing()
    {
        $config = new Config();
        $config->extend(array('\Some\Class' => array('shared' => true)));
        $this->assertTrue($config->isShared('Some\Class'));
    }
}
