<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Controller\Adminhtml\Session;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Test\Unit\Controller\Adminhtml\Session\Check testing
 */
class CheckTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  \Magento\Security\Test\Unit\Controller\Adminhtml\Session\Check
     */
    protected $controller;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \Magento\Security\Model\AdminSessionInfo
     */
    protected $currentSession;

    /**
     * @var \Magento\Security\Model\AdminSessionsManager
     */
    protected $sessionsManager;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->jsonFactory =  $this->getMock(
            '\Magento\Framework\Controller\Result\JsonFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->sessionsManager =  $this->getMock(
            '\Magento\Security\Model\AdminSessionsManager',
            ['getCurrentSession'],
            [],
            '',
            false
        );

        $this->currentSession =  $this->getMock(
            '\Magento\Security\Model\AdminSessionInfo',
            ['isActive'],
            [],
            '',
            false
        );

        $this->controller = $this->objectManager->getObject(
            '\Magento\Security\Controller\Adminhtml\Session\Check',
            [
                'jsonFactory' => $this->jsonFactory,
                'sessionsManager' => $this->sessionsManager
            ]
        );
    }

    /**
     * @param bool $result
     * @dataProvider dataProviderTestExecute
     */
    public function testExecute($result)
    {
        $resultExpectation = [
            'isActive' => $result
        ];
        $jsonMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionsManager->expects($this->any())->method('getCurrentSession')->willReturn($this->currentSession);
        $this->currentSession->expects($this->any())->method('isActive')
            ->will($this->returnValue($result));
        $this->jsonFactory->expects($this->any())->method('create')->willReturn($jsonMock);
        $jsonMock->expects($this->once())
            ->method('setData')
            ->with($resultExpectation)
            ->willReturnSelf();

        $this->assertEquals($jsonMock, $this->controller->execute());
    }

    /**
     * @return array
     */
    public function dataProviderTestExecute()
    {
        return [[true], [false]];
    }
}
