<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Controller\Adminhtml\Rate;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

class AjaxLoadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Executes the controller action and asserts non exception logic
     */
    public function testExecute() {
        $id=1;
        $countryCode = 'US';
        $regionId = 2;

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
                    'id' => $id,
                    'tax_country_id' => $countryCode,
                    'tax_region_id' => $regionId,
                    'tax_postcode' => null,
                    'rate' => 7.5,
                    'code' => 'Tax Rate Code',
                    'titles' => $rateTitles,
                ],
        ]
        );

        $request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $request->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue($id));

        $response = $this->getMockBuilder('\Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods(['representJson'])
            ->getMock();

        $response->expects($this->once())
            ->method('representJson');


        $taxRateRepository = $this->getMockBuilder('\Magento\Tax\Model\Calculation\RateRepository')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $taxRateRepository->expects($this->once())
            ->method('get')
            ->with($id)
            ->will($this->returnValue($rateMock));

        $encode = $this->getMockBuilder('Magento\Framework\Json\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(['jsonEncode'])
            ->getMock();

        $encode->expects($this->once())
            ->method('jsonEncode')
            ->will($this->returnValue(
                [
                    'success' => true,
                    'error_message' => '',
                    'result'=>
                        '{"success":true,"error_message":"","result":{"tax_calculation_rate_id":"1","tax_country_id":"US","tax_region_id":"12","tax_postcode":"*","code":"Rate 1","rate":"8.2500","zip_is_range":0}}'
                ]
            )
            );

        $manager = $this->getMockBuilder('\Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create', 'configure'])
            ->getMock();

        $manager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($encode));


        $notification = $objectManager->getObject(
            'Magento\Tax\Controller\Adminhtml\Rate\AjaxLoad',
            [
                'objectManager' => $manager,
                'taxRateRepository' => $taxRateRepository,
                'request' => $request,
                'response' => $response
            ]
        );

        // No exception thrown
        $notification->execute();
    }

    /**
     * Check if validation throws a catched exception in case of incorrect id
     */
    public function testExecuteException() {
        $id=999;
        $exceptionMessage='No such entity with taxRateId = '.$id;
        $noSuchEntityException= new NoSuchEntityException(__($exceptionMessage));

        $objectManager = new ObjectManager($this);

        $request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $request->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue($id));

        $response = $this->getMockBuilder('\Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods(['representJson'])
            ->getMock();

        $response->expects($this->once())
            ->method('representJson');

        $taxRateRepository = $this->getMockBuilder('\Magento\Tax\Model\Calculation\RateRepository')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $taxRateRepository->expects($this->once())
            ->method('get')
            ->with($id)
            ->willThrowException($noSuchEntityException);

        $encode = $this->getMockBuilder('Magento\Framework\Json\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(['jsonEncode'])
            ->getMock();

        $encode->expects($this->once())
            ->method('jsonEncode');

        $manager = $this->getMockBuilder('\Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create', 'configure'])
            ->getMock();

        $manager->expects($this->once())
            ->method('get')
            ->will($this->returnValue($encode));

        $notification = $objectManager->getObject(
            'Magento\Tax\Controller\Adminhtml\Rate\AjaxLoad',
            [
                'objectManager' => $manager,
                'taxRateRepository' => $taxRateRepository,
                'request' => $request,
                'response' => $response
            ]
        );

        $notification->execute();
    }
}