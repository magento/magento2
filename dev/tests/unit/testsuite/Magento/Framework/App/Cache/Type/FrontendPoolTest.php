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
namespace Magento\Framework\App\Cache\Type;

class FrontendPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Type\FrontendPool
     */
    protected $_model;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\Arguments|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_arguments;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cachePool;

    protected function setUp()
    {
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManager', array(), array(), '', false);
        $this->_arguments = $this->getMock('Magento\Framework\App\Arguments', array(), array(), '', false);
        $this->_cachePool = $this->getMock('Magento\Framework\App\Cache\Frontend\Pool', array(), array(), '', false);
        $this->_model = new FrontendPool(
            $this->_objectManager,
            $this->_arguments,
            $this->_cachePool,
            array('fixture_cache_type' => 'fixture_frontend_id')
        );
    }

    /**
     * @param string|null $fixtureFrontendId
     * @param string $inputCacheType
     * @param string $expectedFrontendId
     *
     * @dataProvider getDataProvider
     */
    public function testGet($fixtureFrontendId, $inputCacheType, $expectedFrontendId)
    {
        $this->_arguments->expects(
            $this->once()
        )->method(
            'getCacheTypeFrontendId'
        )->with(
            $inputCacheType
        )->will(
            $this->returnValue($fixtureFrontendId)
        );

        $cacheFrontend = $this->getMock('Magento\Framework\Cache\FrontendInterface');
        $this->_cachePool->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $expectedFrontendId
        )->will(
            $this->returnValue($cacheFrontend)
        );

        $accessProxy = $this->getMock('Magento\Framework\App\Cache\Type\AccessProxy', array(), array(), '', false);
        $this->_objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Framework\App\Cache\Type\AccessProxy',
            $this->identicalTo(array('frontend' => $cacheFrontend, 'identifier' => $inputCacheType))
        )->will(
            $this->returnValue($accessProxy)
        );

        $this->assertSame($accessProxy, $this->_model->get($inputCacheType));
        // Result has to be cached in memory
        $this->assertSame($accessProxy, $this->_model->get($inputCacheType));
    }

    public function getDataProvider()
    {
        return array(
            'retrieval from config' => array('configured_frontend_id', 'fixture_cache_type', 'configured_frontend_id'),
            'retrieval from map' => array(null, 'fixture_cache_type', 'fixture_frontend_id'),
            'fallback to default id' => array(
                null,
                'unknown_cache_type',
                \Magento\Framework\App\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID
            )
        );
    }
}
