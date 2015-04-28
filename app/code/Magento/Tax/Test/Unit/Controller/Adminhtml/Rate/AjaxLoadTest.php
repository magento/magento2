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
     * @var \Magento\Framework\App\Request\Http
     */
    private $_request;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $_response;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_manager;

    /**
     * @var \Magento\Tax\Model\Calculation\RateRepository
     */
    private $_taxRateRepository;
    /*
     * test setup
     */
    public function setUp()
    {
        $this->_request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMock();

        $this->_response = $this->getMockBuilder('\Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->setMethods(['representJson'])
            ->getMock();

        $this->_manager = $this->getMockBuilder('\Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['get', 'create', 'configure'])
            ->getMock();

        $this->_taxRateRepository = $this->getMockBuilder('\Magento\Tax\Model\Calculation\RateRepository')
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

        $this->_request->expects($this->any())
            ->method('getParam')
            ->will($this->returnValue($taxRateId));

        $this->_response->expects($this->once())
            ->method('representJson');


        $this->_taxRateRepository->expects($this->any())
            ->method('get')
            ->with($taxRateId)
            ->will($this->returnValue($rateMock));

        $taxRateConverter = $this->getMockBuilder('\Magento\Tax\Model\Calculation\Rate\Converter')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $taxRateConverter->expects($this->any())
            ->method('createArrayFromServiceObject')
            ->with($rateMock, true);

        $encode = $this->getMockBuilder('Magento\Framework\Json\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(['jsonEncode'])
            ->getMock();

        $encode->expects($this->any())
            ->method('jsonEncode')
            ->with(['success' => true, 'error_message' => '', 'result'=>
                [
                'tax_calculation_rate_id' => null,
                'tax_country_id' => 'US',
                'tax_region_id' => 2,
                'tax_postcode' => null,
                'code' => 'Tax Rate Code',
                'rate' => 7.5,
                'zip_is_range'=> 0,
                'title[1]' => 'texas',
                ],
            ]);

        $this->_manager->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue($taxRateConverter));
        $this->_manager->expects($this->at(1))
            ->method('get')
            ->will($this->returnValue($encode));

        $notification = $objectManager->getObject(
            'Magento\Tax\Controller\Adminhtml\Rate\AjaxLoad',
            [
                'objectManager' => $this->_manager,
                'taxRateRepository' => $this->_taxRateRepository,
                'request' => $this->_request,
                'response' => $this->_response
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

        $this->_request->expects($this->any())
            ->method('getParam')
            ->will($this->returnValue($taxRateId));

        $this->_response->expects($this->once())
            ->method('representJson');


        $this->_taxRateRepository->expects($this->any())
            ->method('get')
            ->with($taxRateId)
            ->willThrowException($noSuchEntityEx);

        $encode = $this->getMockBuilder('Magento\Framework\Json\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods(['jsonEncode'])
            ->getMock();

        $encode->expects($this->once())
            ->method('jsonEncode')
            ->with(['success' => false, 'error_message' => $exceptionMessage]);

        $this->_manager->expects($this->any())
            ->method('get')
            ->will($this->returnValue($encode));

        $notification = $objectManager->getObject(
            'Magento\Tax\Controller\Adminhtml\Rate\AjaxLoad',
            [
                'objectManager' => $this->_manager,
                'taxRateRepository' => $this->_taxRateRepository,
                'request' => $this->_request,
                'response' => $this->_response
            ]
        );

        //exception thrown with catch
        $notification->execute();
    }
}
