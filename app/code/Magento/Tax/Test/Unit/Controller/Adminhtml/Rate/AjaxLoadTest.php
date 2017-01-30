<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Controller\Adminhtml\Rate;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test for AjaxLoadTest
 */
class AjaxLoadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $resultFactory;

    /**
     * @var \Magento\Tax\Model\Calculation\RateRepository
     */
    private $taxRateRepository;

    /*
     * test setup
     */
    protected function setUp()
    {
        $this->request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->resultFactory = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->taxRateRepository = $this->getMockBuilder('\Magento\Tax\Model\Calculation\RateRepository')
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
            '\Magento\Tax\Model\Calculation\Rate\Title',
            ['data' => ['store_id' => 1, 'value' => 'texas']]
        )
        ];
        $rateMock = $objectManager->getObject(
            'Magento\Tax\Model\Calculation\Rate',
            [
                'data' =>
                    [
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
            ->will($this->returnValue($taxRateId));

        $this->taxRateRepository->expects($this->any())
            ->method('get')
            ->with($taxRateId)
            ->will($this->returnValue($rateMock));

        $taxRateConverter = $this->getMockBuilder('\Magento\Tax\Model\Calculation\Rate\Converter')
            ->disableOriginalConstructor()
            ->getMock();

        $taxRateConverter->expects($this->any())
            ->method('createArrayFromServiceObject')
            ->with($rateMock, true)
            ->willReturn($returnArray);

        $jsonObject= $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();

        $jsonObject->expects($this->once())
            ->method('setData')
            ->with(['success' => true, 'error_message' => '', 'result'=>
                $returnArray,
            ]);

        $this->resultFactory->expects($this->any())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->willReturn($jsonObject);

        $notification = $objectManager->getObject(
            'Magento\Tax\Controller\Adminhtml\Rate\AjaxLoad',
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
        $exceptionMessage='No such entity with taxRateId = '.$taxRateId;
        $noSuchEntityEx= new NoSuchEntityException(__($exceptionMessage));

        $objectManager = new ObjectManager($this);

        $this->request->expects($this->any())
            ->method('getParam')
            ->will($this->returnValue($taxRateId));

        $this->taxRateRepository->expects($this->any())
            ->method('get')
            ->with($taxRateId)
            ->willThrowException($noSuchEntityEx);

        $jsonObject= $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
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
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->willReturn($jsonObject);

        $notification = $objectManager->getObject(
            'Magento\Tax\Controller\Adminhtml\Rate\AjaxLoad',
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
            ->will($this->returnValue($taxRateId));

        $this->taxRateRepository->expects($this->any())
            ->method('get')
            ->with($taxRateId)
            ->willThrowException($noSuchEntityEx);

        $jsonObject= $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
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
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->willReturn($jsonObject);

        $notification = $objectManager->getObject(
            'Magento\Tax\Controller\Adminhtml\Rate\AjaxLoad',
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
