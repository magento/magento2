<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Layout;

/**
 * Class DepersonalizePluginTest
 */
class DepersonalizePluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Module\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleManagerMock;

    /**
     * @var \Magento\PageCache\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheConfigMock;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Persistent\Model\Layout\DepersonalizePlugin
     */
    protected $plugin;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->persistentSessionMock = $this->getMock(
            'Magento\Persistent\Model\Session',
            ['setCustomerId'],
            [],
            '',
            false
        );

        $this->requestMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false,
            true,
            true,
            ['isAjax']
        );
        $this->moduleManagerMock = $this->getMock(
            'Magento\Framework\Module\Manager',
            ['isEnabled'],
            [],
            '',
            false
        );
        $this->cacheConfigMock = $this->getMock(
            'Magento\PageCache\Model\Config',
            ['isEnabled'],
            [],
            '',
            false
        );

        $this->plugin = $this->objectManager->getObject(
            'Magento\Persistent\Model\Layout\DepersonalizePlugin',
            [
                'persistentSession' => $this->persistentSessionMock,
                'request' => $this->requestMock,
                'moduleManager' => $this->moduleManagerMock,
                'cacheConfig' => $this->cacheConfigMock
            ]
        );
    }

    /**
     * Run test afterGenerateXml method
     *
     * @param bool $result
     *
     * @dataProvider dataProviderAfterGenerateXml
     */
    public function testAfterGenerateXml($result)
    {
        /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject $subjectMock */
        $subjectMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\LayoutInterface',
            [],
            '',
            false,
            true,
            true,
            ['isCacheable']
        );
        /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->getMockForAbstractClass(
            'Magento\Framework\View\LayoutInterface',
            [],
            '',
            false,
            true,
            true,
            []
        );

        $this->moduleManagerMock->expects($this->once())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn($result);
        $this->cacheConfigMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($result);
        $this->requestMock->expects($this->any())
            ->method('isAjax')
            ->willReturn(!$result);
        $subjectMock->expects($this->any())
            ->method('isCacheable')
            ->willReturn($result);

        if ($result) {
            $this->persistentSessionMock->expects($this->once())
                ->method('setCustomerId')
                ->with(null);
        } else {
            $this->persistentSessionMock->expects($this->never())
                ->method('setCustomerId')
                ->with(null);
        }

        $this->assertEquals($resultMock, $this->plugin->afterGenerateXml($subjectMock, $resultMock));
    }

    /**
     * Data provider for testAfterGenerateXml
     *
     * @return array
     */
    public function dataProviderAfterGenerateXml()
    {
        return [
            ['result' => true],
            ['result' => false]
        ];
    }
}
