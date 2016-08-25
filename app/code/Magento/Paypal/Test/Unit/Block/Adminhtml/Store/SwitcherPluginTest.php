<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Block\Adminhtml\Store;

use Magento\Paypal\Block\Adminhtml\Store\SwitcherPlugin as StoreSwitcherBlockPlugin;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Backend\Block\Store\Switcher as StoreSwitcherBlock;
use Magento\Framework\App\RequestInterface;
use Magento\Paypal\Model\Config\StructurePlugin as ConfigStructurePlugin;

class SwitcherPluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StoreSwitcherBlockPlugin
     */
    private $plugin;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var StoreSwitcherBlock|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    protected function setUp()
    {
        $this->subjectMock = $this->getMockBuilder(StoreSwitcherBlock::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->plugin = $this->objectManagerHelper->getObject(StoreSwitcherBlockPlugin::class);
    }

    /**
     * @param string|null $countryParam
     * @param array $getUrlParams
     *
     * @dataProvider beforeGetUrlDataProvider
     */
    public function testBeforeGetUrl($countryParam, $getUrlParams)
    {
        $this->requestMock->expects(static::once())
            ->method('getParam')
            ->with(ConfigStructurePlugin::REQUEST_PARAM_COUNTRY)
            ->willReturn($countryParam);
        $this->subjectMock->expects(static::any())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->assertEquals(['', $getUrlParams], $this->plugin->beforeGetUrl($this->subjectMock, '', []));
    }

    /**
     * @return array
     */
    public function beforeGetUrlDataProvider()
    {
        return [
            ['any value', [ConfigStructurePlugin::REQUEST_PARAM_COUNTRY => null]],
            [null, []]
        ];
    }
}
