<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Controller\Adminhtml\Promo\Quote;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Layout;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Generate;
use Magento\SalesRule\Model\CouponGenerator;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenerateTest extends TestCase
{
    /** @var Generate */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var Registry|MockObject */
    protected $registryMock;

    /** @var  MockObject */
    private $requestMock;

    /** @var  MockObject */
    private $messageManager;

    /** @var  MockObject */
    private $responseMock;

    /** @var FileFactory|MockObject */
    protected $fileFactoryMock;

    /** @var  MockObject */
    private $view;

    /** @var Date|MockObject */
    protected $dateMock;

    /** @var  ObjectManager|MockObject */
    private $objectManagerMock;

    /** @var  CouponGenerator|MockObject */
    private $couponGenerator;

    /** @var  CouponGenerationSpecInterfaceFactory|MockObject */
    private $couponGenerationSpec;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this
            ->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this
            ->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->view = $this->getMockForAbstractClass(ViewInterface::class);
        $this->contextMock->expects($this->once())
            ->method('getView')
            ->willReturn($this->view);
        $this->contextMock->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder(FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateMock = $this->getMockBuilder(Date::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->couponGenerator = $this->getMockBuilder(CouponGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->couponGenerationSpec = $this->getMockBuilder(CouponGenerationSpecInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Generate::class,
            [
                'context' => $this->contextMock,
                'coreRegistry' => $this->registryMock,
                'fileFactory' => $this->fileFactoryMock,
                'dateFilter' => $this->dateMock,
                'couponGenerator' => $this->couponGenerator,
                'generationSpecFactory' => $this->couponGenerationSpec
            ]
        );
    }

    /**
     * @covers \Magento\SalesRule\Controller\Adminhtml\Promo\Quote::execute
     */
    public function testExecuteWithCouponTypeAuto()
    {
        $helperData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(Data::class)
            ->willReturn($helperData);
        $requestData = [
            'qty' => 2,
            'length' => 10,
            'rule_id' => 1
        ];
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $ruleMock = $this->getMockBuilder(Rule::class)
            ->addMethods(['getCouponType'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->willReturn($ruleMock);
        $ruleMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $ruleMock->expects($this->once())
            ->method('getCouponType')
            ->willReturn(\Magento\SalesRule\Model\Rule::COUPON_TYPE_AUTO);
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($requestData);
        $requestData['quantity'] = isset($requestData['qty']) ? $requestData['qty'] : null;
        $this->couponGenerationSpec->expects($this->once())
            ->method('create')
            ->with(['data' => $requestData])
            ->willReturn(['some_data', 'some_data_2']);
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage');
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with();
        $helperData->expects($this->once())
            ->method('jsonEncode')
            ->with([
                'messages' => __('%1 coupon(s) have been generated.', 2)
            ]);
        $layout = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->view->expects($this->any())
            ->method('getLayout')
            ->willReturn($layout);
        $messageBlock = $this->getMockBuilder(Messages::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects($this->once())
            ->method('initMessages');
        $layout->expects($this->once())
            ->method('getMessagesBlock')
            ->willReturn($messageBlock);
        $messageBlock->expects($this->once())
            ->method('getGroupedHtml')
            ->willReturn(__('%1 coupon(s) have been generated.', 2));
        $this->model->execute();
    }

    /**
     * @covers \Magento\SalesRule\Controller\Adminhtml\Promo\Quote::execute
     */
    public function testExecuteWithAutoGenerationEnabled()
    {
        $helperData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(Data::class)
            ->willReturn($helperData);
        $requestData = [
            'qty' => 2,
            'length' => 10,
            'rule_id' => 1
        ];
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $ruleMock = $this->getMockBuilder(Rule::class)
            ->addMethods(['getUseAutoGeneration'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->willReturn($ruleMock);
        $ruleMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $ruleMock->expects($this->once())
            ->method('getUseAutoGeneration')
            ->willReturn(1);
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($requestData);
        $requestData['quantity'] = isset($requestData['qty']) ? $requestData['qty'] : null;
        $this->couponGenerationSpec->expects($this->once())
            ->method('create')
            ->with(['data' => $requestData])
            ->willReturn(['some_data', 'some_data_2']);
        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage');
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with();
        $helperData->expects($this->once())
            ->method('jsonEncode')
            ->with([
                'messages' => __('%1 coupon(s) have been generated.', 2)
            ]);
        $layout = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->view->expects($this->any())
            ->method('getLayout')
            ->willReturn($layout);
        $messageBlock = $this->getMockBuilder(Messages::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layout->expects($this->once())
            ->method('initMessages');
        $layout->expects($this->once())
            ->method('getMessagesBlock')
            ->willReturn($messageBlock);
        $messageBlock->expects($this->once())
            ->method('getGroupedHtml')
            ->willReturn(__('%1 coupon(s) have been generated.', 2));
        $this->model->execute();
    }

    /**
     * @covers \Magento\SalesRule\Controller\Adminhtml\Promo\Quote::execute
     */
    public function testExecuteWithCouponTypeNotAutoAndAutoGenerationNotEnabled()
    {
        $helperData = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(Data::class)
        ->willReturn($helperData);
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $ruleMock = $this->getMockBuilder(Rule::class)
            ->addMethods(['getUseAutoGeneration', 'getCouponType'])
            ->onlyMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->willReturn($ruleMock);
        $ruleMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $ruleMock->expects($this->once())
            ->method('getCouponType')
            ->willReturn(\Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON);
        $ruleMock->expects($this->once())
            ->method('getUseAutoGeneration')
            ->willReturn(0);
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with();
        $helperData->expects($this->once())
            ->method('jsonEncode')
            ->with([
                'error' =>
                    __('The rule coupon settings changed. Please save the rule before using auto-generation.')
            ]);
        $this->model->execute();
    }
}
