<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Controller\Adminhtml\Rate;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Exception\NoSuchEntityException;

class AjaxLoadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Retrieve tax rate mock
     *
     * @param array $taxRateData
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getTaxRateMock(array $taxRateData)
    {
        $taxRateMock = $this->getMock('Magento\Tax\Model\Calculation\Rate', [], [], '', false);
        foreach ($taxRateData as $key => $value) {
            // convert key from snake case to upper case
            $taxRateMock->expects($this->once())
                ->method('get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))))
                ->will($this->returnValue($value));
        }

        return $taxRateMock;
    }

    public function testExecute() {

        $id=1;

        $countryCode = 'US';

        $regionId = 2;

        $rateTitles = [];

        $rateMock = $this->getTaxRateMock([
            'id' => $id,
            'tax_country_id' => $countryCode,
            'tax_region_id' => $regionId,
            'tax_postcode' => null,
            'rate' => 7.5,
            'code' => 'Tax Rate Code',
            'titles' => $rateTitles,
        ]);

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
            ->will($this->returnValue($rateMock));

        $encode = $this->getMockBuilder('Magento\Framework\Json\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(['jsonEncode'])
            ->getMock();

        $encode->expects($this->once())
            ->method('jsonEncode')
            ->will($this->returnValue(['success' => true, 'error_message' => '','result'=>'{"success":true,"error_message":"","result":{"tax_calculation_rate_id":"1","tax_country_id":"US","tax_region_id":"12","tax_postcode":"*","code":"US-CA-*-Rate 1","rate":"8.2500","zip_is_range":false}}' ]));

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
        $objectManager = new ObjectManager($this);

        $exception= new NoSuchEntityException(__($exceptionMessage));

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
            ->willThrowException($exception);

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
