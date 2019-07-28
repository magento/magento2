<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\Page;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\Model\ResourceModel\AbstractResource;

/**
 * @covers \Magento\Cms\Model\Page
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $model;

    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var PageResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourcePageMock;

    /**
     * @var AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourcesMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourcePageMock = $this->getMockBuilder(PageResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdFieldName', 'checkIdentifier', 'getResources'])
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourcesMock = $this->getMockBuilder(AbstractResource::class)
            ->setMethods(['getIdFieldName', 'load', 'checkIdentifier'])
            ->getMockForAbstractClass();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);
        $this->resourcePageMock->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourcesMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->model = $objectManager->getObject(
            Page::class,
            [
                'context' => $this->contextMock,
                'resource' => $this->resourcesMock,
            ]
        );
        $objectManager->setBackwardCompatibleProperty(
            $this->model,
            'scopeConfig',
            $this->scopeConfigMock
        );
    }

    /**
     * @covers \Magento\Cms\Model\Page::noRoutePage
     */
    public function testNoRoutePage()
    {
        $this->assertEquals($this->model, $this->model->noRoutePage());
    }

    /**
     * @covers \Magento\Cms\Model\Page::checkIdentifier
     */
    public function testCheckIdentifier()
    {
        $identifier = 1;
        $storeId = 2;
        $fetchOneResult = 'some result';

        $this->resourcesMock->expects($this->atLeastOnce())
            ->method('checkIdentifier')
            ->with($identifier, $storeId)
            ->willReturn($fetchOneResult);

        $this->assertInternalType('string', $this->model->checkIdentifier($identifier, $storeId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage This identifier is reserved for "CMS No Route Page" in configuration.
     */
    public function testBeforeSave404Identifier()
    {
        $this->model->setId(1);
        $this->model->setOrigData('identifier', 'no-route');
        $this->model->setIdentifier('no-route2');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        \Magento\Cms\Helper\Page::XML_PATH_NO_ROUTE_PAGE,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'no-route'
                    ]
                ]
            );

        $this->model->beforeSave();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage This identifier is reserved for "CMS Home Page" in configuration.
     */
    public function testBeforeSaveHomeIdentifier()
    {
        $this->model->setId(1);
        $this->model->setOrigData('identifier', 'home');
        $this->model->setIdentifier('home2');

        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        \Magento\Cms\Helper\Page::XML_PATH_HOME_PAGE,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'home'
                    ]
                ]
            );

        $this->model->beforeSave();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage This identifier is reserved for "CMS No Cookies Page" in configuration.
     */
    public function testBeforeSaveNoCookiesIdentifier()
    {
        $this->model->setId(1);
        $this->model->setOrigData('identifier', 'no-cookies');
        $this->model->setIdentifier('no-cookies2');

        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        \Magento\Cms\Helper\Page::XML_PATH_NO_COOKIES_PAGE,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'no-cookies'
                    ]
                ]
            );

        $this->model->beforeSave();
    }
}
