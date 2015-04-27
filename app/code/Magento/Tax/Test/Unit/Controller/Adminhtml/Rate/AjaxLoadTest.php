<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Executes the controller action and asserts non exception logic
     */
    public function testExecute()
    {
        $taxRateId=1;
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
                        'id' => $taxRateId,
                        'tax_country_id' => 'US',
                        'tax_region_id' => 2,
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
            ->will($this->returnValue($taxRateId));

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
            ->with($taxRateId)
            ->will($this->returnValue($rateMock));

        $taxRateConverter = $this->getMockBuilder('\Magento\Tax\Model\Calculation\Rate\Converter')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $taxRateConverter->expects($this->any())
            ->method('createSimpleArrayFromServiceObject')
            ->with($rateMock);

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
        $manager->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue($taxRateConverter));
        $manager->expects($this->at(1))
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
    public function testExecuteException()
    {
        $taxRateId=999;
        $exceptionMessage='No such entity with taxRateId = '.$taxRateId;
        $noSuchEntityEx= new NoSuchEntityException(__($exceptionMessage));

        $objectManager = new ObjectManager($this);

        $request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $request->expects($this->once())
            ->method('getParam')
            ->will($this->returnValue($taxRateId));

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
            ->with($taxRateId)
            ->willThrowException($noSuchEntityEx);

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
