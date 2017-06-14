<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Controller\Adminhtml\Rule;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Model\Rate\Provider as RatesProvider;
use Magento\Tax\Controller\Adminhtml\Rule\AjaxLoadRates;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for AjaxLoadTest
 */
class AjaxLoadRatesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request | MockObject
     */
    private $request;

    /**
     * @var Response | MockObject
     */
    private $resultFactory;

    /**
     * @var RatesProvider | MockObject
     */
    private $ratesProvider;

    /**
     * @var Context | MockObject
     */
    private $context;

    /**
     * @var SearchCriteriaBuilder | MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setPageSize', 'setCurrentPage', 'create'])
            ->getMock();

        $this->request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->ratesProvider = $this->getMockBuilder(RatesProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['toOptionArray'])
            ->getMock();
    }

    /**
     * Executes the controller action and asserts an exception logic
     */
    public function testExecute()
    {
        $objectManager = new ObjectManager($this);

        $this->request->expects($this->any())
            ->method('getParam')
            ->withAnyParameters()
            ->willReturn('');

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('setCurrentPage')
            ->willReturnSelf();

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('setPageSize')
            ->willReturnSelf();

        $searchCriteria = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->getMockForAbstractClass();

        $this->searchCriteriaBuilder->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);

        $this->ratesProvider->expects($this->any())
            ->method('toOptionArray')
            ->with($searchCriteria)
            ->willThrowException(new \Exception());

        $jsonObject= $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();

        $jsonObject->expects($this->once())
            ->method('setData')
            ->with([
                'success' => false,
                'errorMessage' => __('An error occurred while loading tax rates.')
            ]);

        $this->resultFactory->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($jsonObject);

        $controller = $objectManager->getObject(
            AjaxLoadRates::class,
            [
                'context' => $this->context,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'ratesProvider' => $this->ratesProvider,
                'resultFactory' => $this->resultFactory,
                '_request' => $this->request
            ]
        );

        $this->assertSame($jsonObject, $controller->execute());
    }
}
