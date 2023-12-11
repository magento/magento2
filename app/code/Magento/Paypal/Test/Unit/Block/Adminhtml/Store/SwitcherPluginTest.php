<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Block\Adminhtml\Store;

use Magento\Backend\Block\Store\Switcher as StoreSwitcherBlock;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Paypal\Block\Adminhtml\Store\SwitcherPlugin as StoreSwitcherBlockPlugin;
use Magento\Paypal\Model\Config\StructurePlugin as ConfigStructurePlugin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SwitcherPluginTest extends TestCase
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
     * @var StoreSwitcherBlock|MockObject
     */
    private $subjectMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    protected function setUp(): void
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
