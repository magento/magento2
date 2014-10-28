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
namespace Magento\Framework\App;

class ArgumentsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected static $fixtureConfig;

    /**
     * @var array
     */
    protected static $fixtureConfigMerged;

    /**
     * @var \Magento\Framework\App\Arguments
     */
    protected $_arguments;

    /**
     * @var \Magento\Framework\App\Arguments
     */
    protected $_argumentsMerged;

    public static function setUpBeforeClass()
    {
        self::$fixtureConfig = require __DIR__ . '/Arguments/_files/local.php';
        self::$fixtureConfigMerged = require __DIR__ . '/Arguments/_files/other/local_developer_merged.php';
    }

    protected function setUp()
    {
        $loader = $this->getMock('Magento\Framework\App\Arguments\Loader', array(), array(), '', false);
        $loader->expects($this->atLeastOnce())->method('load')->will($this->returnValue(self::$fixtureConfig));

        $this->_arguments = new \Magento\Framework\App\Arguments(array(), $loader);
        $this->_argumentsMerged = new \Magento\Framework\App\Arguments(
            require __DIR__ . '/Arguments/_files/other/local_developer.php',
            $loader
        );
    }

    /**
     * @param string $connectionName
     * @param bool $testMerged
     * @param array|null $expectedResult
     * @dataProvider getConnectionDataProvider
     */
    public function testGetConnection($connectionName, $testMerged, $expectedResult)
    {
        $arguments = $testMerged ? $this->_argumentsMerged : $this->_arguments;
        $this->assertEquals($expectedResult, $arguments->getConnection($connectionName));
    }

    public function getConnectionDataProvider()
    {
        return array(
            'existing connection' => array(
                'connection_one',
                false,
                array('name' => 'connection_one', 'dbName' => 'db_one')
            ),
            'unknown connection' => array('connection_new', false, null),
            'existing connection, added' => array(
                'connection_new',
                true,
                array('name' => 'connection_new', 'dbName' => 'db_new')
            ),
            'existing connection, overridden' => array(
                'connection_one',
                true,
                array('name' => 'connection_one', 'dbName' => 'overridden_db_one')
            )
        );
    }

    public function testGetConnections()
    {
        $this->assertEquals(self::$fixtureConfig['connection'], $this->_arguments->getConnections());
        $this->assertEquals(self::$fixtureConfigMerged['connection'], $this->_argumentsMerged->getConnections());
    }

    public function testGetResources()
    {
        $this->assertEquals(self::$fixtureConfig['resource'], $this->_arguments->getResources());
        $this->assertEquals(self::$fixtureConfigMerged['resource'], $this->_argumentsMerged->getResources());
    }

    public function testGetCacheFrontendSettings()
    {
        $this->assertEquals(self::$fixtureConfig['cache']['frontend'], $this->_arguments->getCacheFrontendSettings());
        $this->assertEquals(
            self::$fixtureConfigMerged['cache']['frontend'],
            $this->_argumentsMerged->getCacheFrontendSettings()
        );
    }

    /**
     * @param string $cacheType
     * @param bool $testMerged
     * @param string|null $expectedResult
     * @dataProvider getCacheTypeFrontendIdDataProvider
     */
    public function testGetCacheTypeFrontendId($cacheType, $testMerged, $expectedResult)
    {
        $arguments = $testMerged ? $this->_argumentsMerged : $this->_arguments;
        $this->assertEquals($expectedResult, $arguments->getCacheTypeFrontendId($cacheType));
    }

    public function getCacheTypeFrontendIdDataProvider()
    {
        return array(
            'existing cache type' => array('cache_type_one', false, 'cache_frontend_one'),
            'unknown cache type' => array('cache_type_new', false, null),
            'existing cache type, added' => array('cache_type_new', true, 'cache_frontend_two'),
            'existing cache type, overridden' => array('cache_type_one', true, 'cache_frontend_new')
        );
    }
}
