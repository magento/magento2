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
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\ObjectManager\Test
 */
namespace Magento\Test;

class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Expected instance manager parametrized cache after clear
     *
     * @var array
     */
    protected $_instanceCache = array(
        'hashShort' => array(),
        'hashLong'  => array()
    );

    public function testClearCache()
    {
        $resource = new \stdClass;
        $instanceConfig = new \Magento\TestFramework\ObjectManager\Config();
        $verification = $this->getMock(
            'Magento\App\Filesystem\DirectoryList\Verification', array(), array(), '', false
        );
        $cache = $this->getMock('Magento\App\CacheInterface');
        $configLoader = $this->getMock('Magento\App\ObjectManager\ConfigLoader', array(), array(), '', false);
        $configCache = $this->getMock('Magento\App\ObjectManager\ConfigCache', array(), array(), '', false);
        $primaryLoaderMock = $this->getMock(
            'Magento\App\ObjectManager\ConfigLoader\Primary', array(), array(), '', false
        );
        $factory = $this->getMock('\Magento\ObjectManager\Factory', array(), array(), '', false);
        $factory->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnCallback(function ($className) {
                if ($className === 'Magento\Object') {
                    return $this->getMock('Magento\Object', array(), array(), '', false);
                }
            }));

        $model = new \Magento\TestFramework\ObjectManager(
            $factory, $instanceConfig,
            array(
                'Magento\App\Filesystem\DirectoryList\Verification' => $verification,
                'Magento\App\Cache\Type\Config' => $cache,
                'Magento\App\ObjectManager\ConfigLoader' => $configLoader,
                'Magento\App\ObjectManager\ConfigCache' => $configCache,
                'Magento\Config\ReaderInterface' => $this->getMock('Magento\Config\ReaderInterface'),
                'Magento\Config\ScopeInterface' => $this->getMock('Magento\Config\ScopeInterface'),
                'Magento\Config\CacheInterface' => $this->getMock('Magento\Config\CacheInterface'),
                'Magento\Cache\FrontendInterface' => $this->getMock('Magento\Cache\FrontendInterface'),
                'Magento\App\Resource' => $this->getMock(
                    'Magento\App\Resource', array(), array(), '', false
                ),
                'Magento\App\Resource\Config' => $this->getMock(
                    'Magento\App\Resource\Config', array(), array(), '', false
                ),
            ),
            $primaryLoaderMock
        );

        $model->addSharedInstance($resource, 'Magento\App\Resource');
        $instance1 = $model->get('Magento\Object');

        $this->assertSame($instance1, $model->get('Magento\Object'));
        $this->assertSame($model, $model->clearCache());
        $this->assertSame($model, $model->get('Magento\ObjectManager'));
        $this->assertSame($resource, $model->get('Magento\App\Resource'));
        $this->assertNotSame($instance1, $model->get('Magento\Object'));
    }
}
