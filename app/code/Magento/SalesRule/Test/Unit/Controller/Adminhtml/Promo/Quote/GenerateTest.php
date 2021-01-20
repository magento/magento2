<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Controller\Adminhtml\Promo\Quote;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\SalesRule\Model\CouponGenerator;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenerateTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Generate */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit\Framework\MockObject\MockObject */
    protected $contextMock;

    /** @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject */
    protected $registryMock;

    /** @var  \PHPUnit\Framework\MockObject\MockObject */
    private $requestMock;

    /** @var  \PHPUnit\Framework\MockObject\MockObject */
    private $messageManager;

    /** @var  \PHPUnit\Framework\MockObject\MockObject */
    private $responseMock;

    /** @var \Magento\Framework\App\Response\Http\FileFactory|\PHPUnit\Framework\MockObject\MockObject */
    protected $fileFactoryMock;

    /** @var  \PHPUnit\Framework\MockObject\MockObject */
    private $view;

    /** @var \Magento\Framework\Stdlib\DateTime\Filter\Date|\PHPUnit\Framework\MockObject\MockObject */
    protected $dateMock;

    /** @var  ObjectManager | \PHPUnit\Framework\MockObject\MockObject */
    private $objectManagerMock;

    /** @var  CouponGenerator | \PHPUnit\Framework\MockObject\MockObject */
    private $couponGenerator;

    /** @var  CouponGenerationSpecInterfaceFactory | \PHPUnit\Framework\MockObject\MockObject */
    private $couponGenerationSpec;

    /**
     * Test setup
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this
            ->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this
            ->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $this->objectManagerMock = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->view = $this->createMock(\Magento\Framework\App\ViewInterface::class);
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
        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileFactoryMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\Filter\Date::class)
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
            \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Generate::class,
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
     * testExecute
     */
    public function testExecute()
    {
        $helperData = $this->getMockBuilder(\Magento\Framework\Json\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->any())
            ->method('get')
            ->with(\Magento\Framework\Json\Helper\Data::class)
            ->willReturn($helperData);
        $requestData = [
            'qty' => 2,
            'length' => 10,
            'rule_id' => 1
        ];
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn(true);
        $ruleMock = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->willReturn($ruleMock);
        $ruleMock->expects($this->once())
            ->method('getId')
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
        $layout = $this->getMockBuilder(\Magento\Framework\View\Layout::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->view->expects($this->any())
            ->method('getLayout')
            ->willReturn($layout);
        $messageBlock = $this->getMockBuilder(\Magento\Framework\View\Element\Messages::class)
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
