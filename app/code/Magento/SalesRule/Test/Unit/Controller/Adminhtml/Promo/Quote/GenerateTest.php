<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Controller\Adminhtml\Promo\Quote;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Layout;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Generate;
use Magento\SalesRule\Model\CouponGenerator;
use Magento\SalesRule\Model\Quote\GetCouponCodeLengthInterface;
use Magento\SalesRule\Model\Rule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Response\Http as HttpResponse;

/**
 * Class for testing coupon generation
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class GenerateTest extends TestCase
{
    /** @const XML_COUPON_QUANTITY_LIMIT_PATH_TEST */
    private const XML_COUPON_QUANTITY_LIMIT_PATH_TEST = 'promo/auto_generated_coupon_codes/quantity_limit';

    /** @const XML_COUPON_QUANTITY_LIMIT_VALUE_TEST */
    private const XML_COUPON_QUANTITY_LIMIT_VALUE_TEST = 250000;

    /** @const XML_COUPON_QUANTITY_LIMIT_DISABLE_VALUE_TEST */
    private const XML_COUPON_QUANTITY_LIMIT_DISABLE_VALUE_TEST = 0;

    /**
     * @var array
     */
    private array $requestMockData = [
        'qty' => 2,
        'length' => 10,
        'rule_id' => 1
    ];

    /**
     * @var array
     */
    private array $requestMockDataWithInvalidCouponQuantity = [
        'qty' => 250001,
        'length' => 10,
        'rule_id' => 1
    ];

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

    /** @var  PublisherInterface|MockObject */
    private $publisherMock;

    /** @var  CouponGenerationSpecInterfaceFactory|MockObject */
    private $couponGenerationSpec;

    /**
     * @var GetCouponCodeLengthInterface|MockObject
     */
    private $getCouponCodeLength;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this
            ->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this
            ->getMockBuilder(HttpResponse::class)
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
        $this->publisherMock = $this->getMockBuilder(PublisherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->couponGenerationSpec = $this->getMockBuilder(CouponGenerationSpecInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->getCouponCodeLength = $this->getMockBuilder(
            GetCouponCodeLengthInterface::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Generate::class,
            [
                'context' => $this->contextMock,
                'coreRegistry' => $this->registryMock,
                'fileFactory' => $this->fileFactoryMock,
                'dateFilter' => $this->dateMock,
                'couponGenerator' => $this->couponGenerator,
                'publisher' => $this->publisherMock,
                'generationSpecFactory' => $this->couponGenerationSpec,
                'getCouponCodeLength' => $this->getCouponCodeLength,
                'scopeConfig' => $this->scopeConfigMock
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
            ->willReturn(Rule::COUPON_TYPE_AUTO);
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($this->requestMockData);
        $this->requestMockData['quantity'] = $this->requestMockData['qty'] ?? 0;
        $this->couponGenerationSpec->expects($this->any())
            ->method('create')
            ->with(['data' => $this->requestMockData])
            ->willReturn(['some_data', 'some_data_2']);
        $this->getCouponCodeLength->expects($this->once())
            ->method('fetchCouponCodeLength')
            ->willReturn(10);
        $this->messageManager->expects($this->any())
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
        $this->getCouponCodeLength->expects($this->once())
            ->method('fetchCouponCodeLength')
            ->willReturn(10);
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($this->requestMockData);
        $this->requestMockData['quantity'] = $this->requestMockData['qty'] ?? 0;
        $this->couponGenerationSpec->expects($this->any())
            ->method('create')
            ->with(['data' => $this->requestMockData])
            ->willReturn(['some_data', 'some_data_2']);
        $this->messageManager->expects($this->any())
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
            ->willReturn(Rule::COUPON_TYPE_NO_COUPON);
        $ruleMock->expects($this->once())
            ->method('getUseAutoGeneration')
            ->willReturn(0);
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with();
        $helperData->expects($this->once())
            ->method('jsonEncode')
            ->with([
                'error' => __('The rule coupon settings changed. Please save the rule before using auto-generation.')
            ]);
        $this->model->execute();
    }

    /**
     * @covers \Magento\SalesRule\Controller\Adminhtml\Promo\Quote::execute
     */
    public function testExecuteWithInvalidCouponQuantity()
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
            ->willReturn(Rule::COUPON_TYPE_AUTO);
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($this->requestMockDataWithInvalidCouponQuantity);
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(self::XML_COUPON_QUANTITY_LIMIT_PATH_TEST)
            ->willReturn(self::XML_COUPON_QUANTITY_LIMIT_VALUE_TEST);
        $this->getCouponCodeLength->expects($this->once())
            ->method('fetchCouponCodeLength')
            ->willReturn(10);
        $this->messageManager->expects($this->once())
            ->method('addErrorMessage');
        $this->responseMock->expects($this->once())
            ->method('representJson')
            ->with();
        $helperData->expects($this->once())
            ->method('jsonEncode')
            ->with([
                'messages' => __(
                    'Coupon qty should be less than or equal to the coupon qty in the store configuration.'
                )
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
            ->willReturn(__('Coupon qty should be less than or equal to the coupon qty in the store configuration.'));
        $this->model->execute();
    }

    /**
     * @covers \Magento\SalesRule\Controller\Adminhtml\Promo\Quote::execute
     */
    public function testExecuteWithDisableCouponQuantity()
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
            ->willReturn(Rule::COUPON_TYPE_AUTO);
        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($this->requestMockDataWithInvalidCouponQuantity);
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('getValue')
            ->with(self::XML_COUPON_QUANTITY_LIMIT_PATH_TEST)
            ->willReturn(self::XML_COUPON_QUANTITY_LIMIT_DISABLE_VALUE_TEST);
        $this->requestMockDataWithInvalidCouponQuantity['quantity'] = $this->requestMockDataWithInvalidCouponQuantity['qty'] ?? 0;//phpcs:ignore
        $this->couponGenerationSpec->expects($this->any())
            ->method('create')
            ->with(['data' => $this->requestMockDataWithInvalidCouponQuantity])
            ->willReturn(['some_data', 'some_data_2']);
        $this->getCouponCodeLength->expects($this->once())
            ->method('fetchCouponCodeLength')
            ->willReturn(10);
        $this->messageManager->expects($this->any())
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
}
