<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Controller\Adminhtml\Rate;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Controller\Adminhtml\Rate\AjaxLoad;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\Calculation\Rate\Converter;
use Magento\Tax\Model\Calculation\Rate\Title;
use Magento\Tax\Model\Calculation\RateRepository;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AjaxLoadTest extends TestCase
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $resultFactory;

    /**
     * @var RateRepository
     */
    private $taxRateRepository;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->taxRateRepository = $this->getMockBuilder(RateRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
    }

    /**
     * Executes the controller action and asserts non exception logic
     */
    public function testExecute()
    {
        $taxRateId=1;
        $returnArray=[
            'tax_calculation_rate_id' => null,
            'tax_country_id' => 'US',
            'tax_region_id' => 2,
            'tax_postcode' => null,
            'code' => 'Tax Rate Code',
            'rate' => 7.5,
            'zip_is_range'=> 0,
            'title[1]' => 'texas',
        ];
        $objectManager = new ObjectManager($this);
        $rateTitles = [$objectManager->getObject(
            Title::class,
            ['data' => ['store_id' => 1, 'value' => 'texas']]
        )
        ];
        $rateMock = $objectManager->getObject(
            Rate::class,
            [
                'data' => [
                    'tax_country_id' => 'US',
                    'tax_region_id' => 2,
                    'tax_postcode' => null,
                    'rate' => 7.5,
                    'code' => 'Tax Rate Code',
                    'titles' => $rateTitles,
                ],
            ]
        );

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn($taxRateId);

        $this->taxRateRepository->expects($this->any())
            ->method('get')
            ->with($taxRateId)
            ->willReturn($rateMock);

        $taxRateConverter = $this->getMockBuilder(Converter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $taxRateConverter->expects($this->any())
            ->method('createArrayFromServiceObject')
            ->with($rateMock, true)
            ->willReturn($returnArray);

        $jsonObject= $this->getMockBuilder(JsonResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();

        $jsonObject->expects($this->once())
            ->method('setData')
            ->with(['success' => true, 'error_message' => '', 'result'=> $returnArray,
            ]);

        $this->resultFactory->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($jsonObject);

        $notification = $objectManager->getObject(
            AjaxLoad::class,
            [
                'taxRateRepository' => $this->taxRateRepository,
                'taxRateConverter' => $taxRateConverter,
                'request' => $this->request,
                'resultFactory' => $this->resultFactory,
            ]
        );

        // No exception thrown
        $this->assertSame($jsonObject, $notification->execute());
    }

    /**
     * Check if validation throws a localized catched exception in case of incorrect id
     */
    public function testExecuteLocalizedException()
    {
        $taxRateId=999;
        $exceptionMessage='No such entity with taxRateId = ' . $taxRateId;
        $noSuchEntityEx= new NoSuchEntityException(__($exceptionMessage));

        $objectManager = new ObjectManager($this);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn($taxRateId);

        $this->taxRateRepository->expects($this->any())
            ->method('get')
            ->with($taxRateId)
            ->willThrowException($noSuchEntityEx);

        $jsonObject= $this->getMockBuilder(JsonResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();

        $jsonObject->expects($this->once())
            ->method('setData')
            ->with([
                'success' => false,
                'error_message' => $exceptionMessage,
            ]);

        $this->resultFactory->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($jsonObject);

        $notification = $objectManager->getObject(
            AjaxLoad::class,
            [
                'taxRateRepository' => $this->taxRateRepository,
                'request' => $this->request,
                'resultFactory' => $this->resultFactory,
            ]
        );

        //exception thrown with catch
        $this->assertSame($jsonObject, $notification->execute());
    }

    /**
     * Check if validation throws a localized catched exception in case of incorrect id
     */
    public function testExecuteException()
    {
        $taxRateId=999;
        $exceptionMessage=__('An error occurred while loading this tax rate.');
        $noSuchEntityEx= new \Exception();

        $objectManager = new ObjectManager($this);

        $this->request->expects($this->any())
            ->method('getParam')
            ->willReturn($taxRateId);

        $this->taxRateRepository->expects($this->any())
            ->method('get')
            ->with($taxRateId)
            ->willThrowException($noSuchEntityEx);

        $jsonObject= $this->getMockBuilder(JsonResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();

        $jsonObject->expects($this->once())
            ->method('setData')
            ->with([
                'success' => false,
                'error_message' => $exceptionMessage,
            ]);

        $this->resultFactory->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($jsonObject);

        $notification = $objectManager->getObject(
            AjaxLoad::class,
            [
                'taxRateRepository' => $this->taxRateRepository,
                'request' => $this->request,
                'resultFactory' => $this->resultFactory,
            ]
        );

        //exception thrown with catch
        $this->assertSame($jsonObject, $notification->execute());
    }
}
