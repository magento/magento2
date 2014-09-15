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
namespace Magento\Framework\App\Cache;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Cache\State\Options|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheFrontend;

    /**
     * @param string $cacheType
     * @param array $typeOptions
     * @param bool $banAll
     * @param bool $expectedIsEnabled
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($cacheType, $typeOptions, $banAll, $expectedIsEnabled)
    {
        $model = $this->_buildModel($typeOptions, array(), $banAll);
        $actualIsEnabled = $model->isEnabled($cacheType);
        $this->assertEquals($expectedIsEnabled, $actualIsEnabled);
    }

    /**
     * @return array
     */
    public static function isEnabledDataProvider()
    {
        return array(
            'enabled' => array(
                'cacheType' => 'cache_type',
                'typeOptions' => array('some_type' => false, 'cache_type' => true),
                'banAll' => false,
                'expectedIsEnabled' => true
            ),
            'disabled' => array(
                'cacheType' => 'cache_type',
                'typeOptions' => array('some_type' => true, 'cache_type' => false),
                'banAll' => false,
                'expectedIsEnabled' => false
            ),
            'unknown is disabled' => array(
                'cacheType' => 'unknown_cache_type',
                'typeOptions' => array('some_type' => true),
                'banAll' => false,
                'expectedIsEnabled' => false
            ),
            'disabled, when all caches are banned' => array(
                'cacheType' => 'cache_type',
                'typeOptions' => array('cache_type' => true),
                'banAll' => true,
                'expectedIsEnabled' => false
            )
        );
    }

    /**
     * Builds model to be tested
     *
     * @param array|false $cacheTypeOptions
     * @param array|false $resourceTypeOptions
     * @param bool $banAll
     * @return \Magento\Framework\App\Cache\StateInterface
     */
    protected function _buildModel(
        $cacheTypeOptions,
        $resourceTypeOptions = false,
        $banAll = false
    ) {
        $this->_cacheFrontend = $this->getMock('Magento\Framework\Cache\FrontendInterface');
        $this->_cacheFrontend->expects(
            $this->any()
        )->method(
            'load'
        )->with(
            \Magento\Framework\App\Cache\State::CACHE_ID
        )->will(
            $this->returnValue($cacheTypeOptions === false ? false : serialize($cacheTypeOptions))
        );
        $cacheFrontendPool = $this->getMock('Magento\Framework\App\Cache\Frontend\Pool', array(), array(), '', false);
        $cacheFrontendPool->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            \Magento\Framework\App\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID
        )->will(
            $this->returnValue($this->_cacheFrontend)
        );

        $this->_resource = $this->getMock('Magento\Framework\App\Cache\State\Options', array(), array(), '', false);
        $this->_resource->expects(
            $this->any()
        )->method(
            'getAllOptions'
        )->will(
            $this->returnValue($resourceTypeOptions)
        );

        $this->_model = new \Magento\Framework\App\Cache\State(
            $this->_resource,
            $cacheFrontendPool,
            $banAll
        );

        return $this->_model;
    }

    /**
     * The model must fetch data via its resource, if the cache type list is not cached
     * (e.g. cache load result is FALSE)
     */
    public function testIsEnabledFallbackToResource()
    {
        $model = $this->_buildModel(array(), array('cache_type' => true));
        $this->assertFalse($model->isEnabled('cache_type'));

        $model = $this->_buildModel(false, array('cache_type' => true));
        $this->assertTrue($model->isEnabled('cache_type'));
    }

    public function testSetEnabledIsEnabled()
    {
        $model = $this->_buildModel(array('cache_type' => false));
        $model->setEnabled('cache_type', true);
        $this->assertTrue($model->isEnabled('cache_type'));

        $model->setEnabled('cache_type', false);
        $this->assertFalse($model->isEnabled('cache_type'));
    }

    public function testPersist()
    {
        $cacheTypes = array('cache_type' => false);
        $model = $this->_buildModel($cacheTypes);

        $this->_resource->expects($this->once())->method('saveAllOptions')->with($cacheTypes);
        $this->_cacheFrontend->expects($this->once())
            ->method('remove')
            ->with(\Magento\Framework\App\Cache\State::CACHE_ID);

        $model->persist();
    }
}
