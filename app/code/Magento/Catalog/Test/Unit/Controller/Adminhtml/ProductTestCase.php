<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Session;
use Magento\Catalog\Controller\Product;
use Magento\Catalog\Model\Product\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Layout;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class ProductTestCase extends TestCase
{
    /** @var MockObject */
    protected $context;

    /** @var Product */
    protected $action;

    /** @var Layout  */
    protected $layout;

    /** @var Session|MockObject */
    protected $session;

    /** @var Http|MockObject */
    protected $request;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * Init context object
     *
     * @param array $additionalParams
     * @param array $objectManagerMap Object Manager mappings
     * @return MockObject
     */
    protected function initContext(array $additionalParams = [], array $objectManagerMap = [])
    {
        $productActionMock = $this->createMock(Action::class);

        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        if ($objectManagerMap) {
            $this->objectManagerMock->expects($this->any())
                ->method('get')
                ->willReturnMap($objectManagerMap);
        }

        $this->objectManagerMock->method('get')->willReturn($productActionMock);

        $block = $this->createMock(AbstractBlock::class);
        $this->layout = $this->createPartialMock(Layout::class, ['getBlock']);
        $this->layout->method('getBlock')->willReturn($block);

        $eventManager = $this->createPartialMock(Manager::class, ['dispatch']);
        $eventManager->expects($this->any())->method('dispatch')->willReturnSelf();
        $requestInterfaceMock = $this->createPartialMock(
            Http::class,
            ['getParam', 'getPost', 'getFullActionName', 'getPostValue']
        );

        $responseInterfaceMock = $this->createMock(ResponseInterface::class);

        $managerInterfaceMock = $this->createMock(ManagerInterface::class);
        $sessionMock = $this->createMock(Session::class);
        $actionFlagMock = $this->createMock(ActionFlag::class);
        $helperDataMock = $this->createMock(Data::class);
        $this->context = $this->createMock(Context::class);

        $this->context->method('getEventManager')->willReturn($eventManager);
        $this->context->method('getRequest')->willReturn($requestInterfaceMock);
        $this->context->method('getResponse')->willReturn($responseInterfaceMock);
        $this->context->method('getObjectManager')->willReturn($this->objectManagerMock);

        $this->context->method('getMessageManager')->willReturn($managerInterfaceMock);
        $this->context->method('getSession')->willReturn($sessionMock);
        $this->context->method('getActionFlag')->willReturn($actionFlagMock);
        $this->context->method('getHelper')->willReturn($helperDataMock);

        foreach ($additionalParams as $property => $object) {
            $this->context->expects($this->any())->method('get' . ucfirst($property))->willReturn($object);
        }

        $this->session = $sessionMock;
        $this->request = $requestInterfaceMock;

        return $this->context;
    }
}
