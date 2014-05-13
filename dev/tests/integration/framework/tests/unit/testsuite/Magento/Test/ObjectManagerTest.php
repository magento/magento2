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

/**
 * Test class for \Magento\Framework\ObjectManager\Test
 */
namespace Magento\Test;

class ObjectManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Expected instance manager parametrized cache after clear
     *
     * @var array
     */
    protected $_instanceCache = array('hashShort' => array(), 'hashLong' => array());

    public function testClearCache()
    {
        $resource = new \stdClass();
        $instanceConfig = new \Magento\TestFramework\ObjectManager\Config();
        $verification = $this->getMock(
            'Magento\Framework\App\Filesystem\DirectoryList\Verification',
            array(),
            array(),
            '',
            false
        );
        $cache = $this->getMock('Magento\Framework\App\CacheInterface');
        $configLoader = $this->getMock('Magento\Framework\App\ObjectManager\ConfigLoader', array(), array(), '', false);
        $configCache = $this->getMock('Magento\Framework\App\ObjectManager\ConfigCache', array(), array(), '', false);
        $primaryLoaderMock = $this->getMock(
            'Magento\Framework\App\ObjectManager\ConfigLoader\Primary',
            array(),
            array(),
            '',
            false
        );
        $factory = $this->getMock('\Magento\Framework\ObjectManager\Factory', array(), array(), '', false);
        $factory->expects($this->exactly(2))->method('create')->will(
            $this->returnCallback(
                function ($className) {
                    if ($className === 'Magento\Framework\Object') {
                        return $this->getMock('Magento\Framework\Object', array(), array(), '', false);
                    }
                }
            )
        );

        $model = new \Magento\TestFramework\ObjectManager(
            $factory,
            $instanceConfig,
            array(
                'Magento\Framework\App\Filesystem\DirectoryList\Verification' => $verification,
                'Magento\Framework\App\Cache\Type\Config' => $cache,
                'Magento\Framework\App\ObjectManager\ConfigLoader' => $configLoader,
                'Magento\Framework\App\ObjectManager\ConfigCache' => $configCache,
                'Magento\Framework\Config\ReaderInterface' => $this->getMock(
                    'Magento\Framework\Config\ReaderInterface'
                ),
                'Magento\Framework\Config\ScopeInterface' => $this->getMock('Magento\Framework\Config\ScopeInterface'),
                'Magento\Framework\Config\CacheInterface' => $this->getMock('Magento\Framework\Config\CacheInterface'),
                'Magento\Framework\Cache\FrontendInterface' =>
                    $this->getMock('Magento\Framework\Cache\FrontendInterface'),
                'Magento\Framework\App\Resource' => $this->getMockBuilder('Magento\Framework\App\Resource')
                        ->disableOriginalConstructor()
                        ->getMock(),
                'Magento\Framework\App\Resource\Config' => $this->getMock(
                    'Magento\Framework\App\Resource\Config',
                    array(),
                    array(),
                    '',
                    false
                )
            ),
            $primaryLoaderMock
        );

        $model->addSharedInstance($resource, 'Magento\Framework\App\Resource');
        $instance1 = $model->get('Magento\Framework\Object');

        $this->assertSame($instance1, $model->get('Magento\Framework\Object'));
        $this->assertSame($model, $model->clearCache());
        $this->assertSame($model, $model->get('Magento\Framework\ObjectManager'));
        $this->assertSame($resource, $model->get('Magento\Framework\App\Resource'));
        $this->assertNotSame($instance1, $model->get('Magento\Framework\Object'));
    }
}
