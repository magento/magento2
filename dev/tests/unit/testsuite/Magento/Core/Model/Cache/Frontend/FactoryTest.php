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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Cache\Frontend;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        require_once __DIR__ . '/FactoryTest/CacheDecoratorDummy.php';
    }

    public function testCreate()
    {
        $model = $this->_buildModelForCreate();
        $result = $model->create(array('backend' => 'Zend_Cache_Backend_BlackHole'));

        $this->assertInstanceOf(
            'Magento\Cache\FrontendInterface',
            $result,
            'Created object must implement \Magento\Cache\FrontendInterface'
        );
        $this->assertInstanceOf(
            'Magento\Cache\Core',
            $result->getLowLevelFrontend(),
            'Created object must have \Magento\Cache\Core frontend by default'
        );
        $this->assertInstanceOf(
            'Zend_Cache_Backend_BlackHole',
            $result->getBackend(),
            'Created object must have backend as configured in backend options'
        );
    }

    public function testCreateOptions()
    {
        $model = $this->_buildModelForCreate();
        $result = $model->create(
            array(
                'backend' => 'Zend_Cache_Backend_Static',
                'frontend_options' => array(
                    'lifetime' => 2601
                ),
                'backend_options' => array(
                    'file_extension' => '.wtf'
                ),
            )
        );

        $frontend = $result->getLowLevelFrontend();
        $backend = $result->getBackend();

        $this->assertEquals(2601, $frontend->getOption('lifetime'));
        $this->assertEquals('.wtf', $backend->getOption('file_extension'));
    }

    public function testCreateEnforcedOptions()
    {
        $model = $this->_buildModelForCreate(array('backend' => 'Zend_Cache_Backend_Static'));
        $result = $model->create(array('backend' => 'Zend_Cache_Backend_BlackHole'));

        $this->assertInstanceOf('Zend_Cache_Backend_Static', $result->getBackend());
    }

    /**
     * @param array $options
     * @param string $expectedPrefix
     * @dataProvider idPrefixDataProvider
     */
    public function testIdPrefix($options, $expectedPrefix)
    {
        $model = $this->_buildModelForCreate(array('backend' => 'Zend_Cache_Backend_Static'));
        $result = $model->create($options);

        $frontend = $result->getLowLevelFrontend();
        $this->assertEquals($expectedPrefix, $frontend->getOption('cache_id_prefix'));
    }

    /**
     * @return array
     */
    public static function idPrefixDataProvider()
    {
        return array(
            'default id prefix' => array(
                array(
                    'backend' => 'Zend_Cache_Backend_BlackHole',
                ),
                'a3c_', // start of md5('CONFIG_DIR')
            ),
            'id prefix in "id_prefix" option' => array(
                array(
                    'backend' => 'Zend_Cache_Backend_BlackHole',
                    'id_prefix' => 'id_prefix_value'
                ),
                'id_prefix_value',
            ),
            'id prefix in "prefix" option' => array(
                array(
                    'backend' => 'Zend_Cache_Backend_BlackHole',
                    'prefix' => 'prefix_value'
                ),
                'prefix_value',
            ),
        );
    }

    public function testCreateDecorators()
    {
        $model = $this->_buildModelForCreate(
            array(),
            array(
                array(
                    'class' => 'Magento\Core\Model\Cache\Frontend\FactoryTest\CacheDecoratorDummy',
                    'parameters' => array('param' => 'value')
                )
            )
        );
        $result = $model->create(array('backend' => 'Zend_Cache_Backend_BlackHole'));

        $this->assertInstanceOf('Magento\Core\Model\Cache\Frontend\FactoryTest\CacheDecoratorDummy', $result);

        $params = $result->getParams();
        $this->assertArrayHasKey('param', $params);
        $this->assertEquals($params['param'], 'value');
    }

    /**
     * Create the model to be tested, providing it with all required dependencies
     *
     * @param array $enforcedOptions
     * @param array $decorators
     * @return \Magento\Core\Model\Cache\Frontend\Factory
     */
    protected function _buildModelForCreate($enforcedOptions = array(), $decorators = array())
    {
        $processFrontendFunc = function ($class, $params) {
            switch ($class) {
                case 'Magento\Cache\Frontend\Adapter\Zend':
                    return new $class($params['frontend']);
                case 'Magento\Core\Model\Cache\Frontend\FactoryTest\CacheDecoratorDummy':
                    $frontend = $params['frontend'];
                    unset($params['frontend']);
                    return new $class($frontend, $params);
                default:
                    throw new \Exception("Test is not designed to create {$class} objects");
                    break;
            }
        };
        /** @var $objectManager \PHPUnit_Framework_MockObject_MockObject */
        $objectManager = $this->getMock('Magento\ObjectManager', array(), array(), '', false);
        $objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnCallback($processFrontendFunc));

        $filesystem = $this->getMock('Magento\Filesystem', array(), array(), '', false);
        $filesystem->expects($this->any())
            ->method('isDirectory')
            ->will($this->returnValue(true));
        $filesystem->expects($this->any())
            ->method('isWritable')
            ->will($this->returnValue(true));

        $map = array(
            array(\Magento\App\Dir::CACHE, 'CACHE_DIR'),
            array(\Magento\App\Dir::CONFIG, 'CONFIG_DIR'),
        );
        $dirs = $this->getMock('Magento\App\Dir', array('getDir'), array(), '', false);
        $resource = $this->getMock('Magento\Core\Model\Resource', array(), array(), '', false);
        $dirs->expects($this->any())
            ->method('getDir')
            ->will($this->returnValueMap($map));

        $model = new \Magento\Core\Model\Cache\Frontend\Factory($objectManager, $filesystem, $dirs, $resource,
            $enforcedOptions, $decorators);

        return $model;
    }
}
