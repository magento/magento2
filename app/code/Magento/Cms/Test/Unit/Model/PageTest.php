<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Cms\Helper\Page as CmsPageHelperPage;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\Cms\Model\Page
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PageTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $model;

    /**
     * @var \Magento\Backend\Block\Template\Context|MockObject
     */
    protected $contextMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var PageResource|MockObject
     */
    protected $resourcePageMock;

    /**
     * @var AbstractResource|MockObject
     */
    protected $resourcesMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourcePageMock = $this->createPartialMockWithReflection(
            PageResource::class,
            ['getResources', 'getIdFieldName', 'checkIdentifier']
        );
        $this->resourcesMock = $this->createPartialMockWithReflection(
            AbstractResource::class,
            ['getIdFieldName', 'load', 'checkIdentifier', '_construct', 'getConnection']
        );
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);

        $this->contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);
        $this->resourcePageMock->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourcesMock);

        $objectManager = new ObjectManager($this);

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

        $this->assertIsString($this->model->checkIdentifier($identifier, $storeId));
        // TODO: After migration to PHPUnit 8, replace deprecated method
        // $this->assertIsString($this->model->checkIdentifier($identifier, $storeId));
    }

    public function testBeforeSave404Identifier()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('This identifier is reserved for "CMS No Route Page" in configuration.');
        $this->model->setId(1);
        $this->model->setOrigData('identifier', 'no-route');
        $this->model->setIdentifier('no-route2');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        CmsPageHelperPage::XML_PATH_NO_ROUTE_PAGE,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'no-route'
                    ]
                ]
            );

        $this->model->beforeSave();
    }

    public function testBeforeSaveHomeIdentifier()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('This identifier is reserved for "CMS Home Page" in configuration.');
        $this->model->setId(1);
        $this->model->setOrigData('identifier', 'home');
        $this->model->setIdentifier('home2');

        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        CmsPageHelperPage::XML_PATH_HOME_PAGE,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'home'
                    ]
                ]
            );

        $this->model->beforeSave();
    }

    public function testBeforeSaveNoCookiesIdentifier()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('This identifier is reserved for "CMS No Cookies Page" in configuration.');
        $this->model->setId(1);
        $this->model->setOrigData('identifier', 'no-cookies');
        $this->model->setIdentifier('no-cookies2');

        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        CmsPageHelperPage::XML_PATH_NO_COOKIES_PAGE,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'no-cookies'
                    ]
                ]
            );

        $this->model->beforeSave();
    }
}
