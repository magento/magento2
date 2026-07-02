<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Controller\Adminhtml\Promo\Catalog;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Backend\App\Action\Context;
use PHPUnit\Framework\TestCase;
use Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog\MassActivate;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;

class MassActivateTest extends TestCase
{
    /**
     * @var CatalogRuleRepositoryInterface|MockObject
     */
    private $catalogRuleRepositoryMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var MassActivate
     */
    protected $activate;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var Rule|MockObject
     */
    protected $ruleMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->contextMock = $this->createMock(Context::class);

        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

        $this->resultRedirectMock = $this->createPartialMock(
            Redirect::class,
            ['setPath']
        );

        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->resultFactory->method('create')->willReturn($this->resultRedirectMock);
        $this->contextMock->method('getResultFactory')->willReturn($this->resultFactory);
        $this->contextMock->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->method('getRequest')->willReturn($this->requestMock);
        $this->catalogRuleRepositoryMock = $this->createMock(
            CatalogRuleRepositoryInterface::class
        );
        $this->activate = new MassActivate($this->contextMock, $this->catalogRuleRepositoryMock);
    }

    public function testExecute()
    {
        $data = [1];
        $this->requestMock->expects(self::any())
            ->method('getParam')
            ->willReturn($data);
        $catalogRuleMock = $this->createMock(RuleInterface::class);
        $this->catalogRuleRepositoryMock->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($catalogRuleMock);
        $this->messageManagerMock->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__('You activated a total of %1 records.', $data));
        $this->activate->execute();
    }
}
