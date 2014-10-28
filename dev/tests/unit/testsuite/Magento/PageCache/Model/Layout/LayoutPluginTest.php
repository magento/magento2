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
namespace Magento\PageCache\Model\Layout;

class LayoutPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\Layout\LayoutPlugin
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\View\Layout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $configMock;

    public function setUp()
    {
        $this->layoutMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\Layout',
            array(),
            '',
            false,
            true,
            true,
            array('isCacheable', 'getAllBlocks')
        );
        $this->responseMock = $this->getMock('\Magento\Framework\App\Response\Http', array(), array(), '', false);
        $this->configMock = $this->getMock('Magento\PageCache\Model\Config', array(), array(), '', false);

        $this->model = new \Magento\PageCache\Model\Layout\LayoutPlugin(
            $this->layoutMock,
            $this->responseMock,
            $this->configMock
        );
    }

    /**
     * @param $cacheState
     * @param $layoutIsCacheable
     * @dataProvider afterGenerateXmlDataProvider
     */
    public function testAfterGenerateXml($cacheState, $layoutIsCacheable)
    {
        $maxAge = 180;
        $result = 'test';

        $this->layoutMock->expects($this->once())->method('isCacheable')->will($this->returnValue($layoutIsCacheable));
        $this->configMock->expects($this->any())->method('isEnabled')->will($this->returnValue($cacheState));

        if ($layoutIsCacheable && $cacheState) {
            $this->configMock->expects($this->once())->method('getTtl')->will($this->returnValue($maxAge));
            $this->responseMock->expects($this->once())->method('setPublicHeaders')->with($maxAge);
        } else {
            $this->responseMock->expects($this->never())->method('setPublicHeaders');
        }
        $output = $this->model->afterGenerateXml($this->layoutMock, $result);
        $this->assertSame($result, $output);
    }

    public function afterGenerateXmlDataProvider()
    {
        return array(
            'Full_cache state is true, Layout is cache-able' => array(true, true),
            'Full_cache state is true, Layout is not cache-able' => array(true, false),
            'Full_cache state is false, Layout is not cache-able' => array(false, false),
            'Full_cache state is false, Layout is cache-able' => array(false, true)
        );
    }

    /**
     * @param $cacheState
     * @param $layoutIsCacheable
     * @param $expectedTags
     * @param $configCacheType
     * @param $ttl
     * @dataProvider afterGetOutputDataProvider
     */
    public function testAfterGetOutput($cacheState, $layoutIsCacheable, $expectedTags, $configCacheType, $ttl)
    {
        $html = 'html';
        $this->configMock->expects($this->any())->method('isEnabled')->will($this->returnValue($cacheState));
        $blockStub = $this->getMock('Magento\PageCache\Block\Controller\StubBlock', null, array(), '', false);
        $blockStub->setTtl($ttl);
        $this->layoutMock->expects($this->once())->method('isCacheable')->will($this->returnValue($layoutIsCacheable));
        $this->layoutMock->expects($this->any())->method('getAllBlocks')->will($this->returnValue(array($blockStub)));

        $this->configMock->expects($this->any())->method('getType')->will($this->returnValue($configCacheType));

        if ($layoutIsCacheable && $cacheState) {
            $this->responseMock->expects($this->once())->method('setHeader')->with('X-Magento-Tags', $expectedTags);
        } else {
            $this->responseMock->expects($this->never())->method('setHeader');
        }
        $output = $this->model->afterGetOutput($this->layoutMock, $html);
        $this->assertSame($output, $html);
    }

    public function afterGetOutputDataProvider()
    {
        $tags = 'identity1,identity2';
        return array(
            'Cacheable layout, Full_cache state is true' => array(true, true, $tags, null, 0),
            'Non-cacheable layout' => array(true, false, null, null, 0),
            'Cacheable layout with Varnish' => array(true, true, $tags, \Magento\PageCache\Model\Config::VARNISH, 0),
            'Cacheable layout with Varnish, Full_cache state is false' => array(
                false,
                true,
                $tags,
                \Magento\PageCache\Model\Config::VARNISH,
                0
            ),
            'Cacheable layout with Varnish and esi' => array(
                true,
                true,
                null,
                \Magento\PageCache\Model\Config::VARNISH,
                100
            ),
            'Cacheable layout with Builtin' => array(true, true, $tags, \Magento\PageCache\Model\Config::BUILT_IN, 0),
            'Cacheable layout with Builtin, Full_cache state is false' => array(
                false,
                true,
                $tags,
                \Magento\PageCache\Model\Config::BUILT_IN,
                0
            ),
            'Cacheable layout with Builtin and esi' => array(
                true,
                false,
                $tags,
                \Magento\PageCache\Model\Config::BUILT_IN,
                100
            )
        );
    }
}
