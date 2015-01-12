<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Model\Integration;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject */
        $mockObjectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');

        $data = [
            Info::DATA_NAME => 'nameTest',
            Info::DATA_ID => '1',
            Info::DATA_EMAIL => 'test@magento.com',
            Info::DATA_ENDPOINT => 'http://magento.ll/endpoint',
        ];
        $mockIntegration = $this->getMockBuilder(
            'Magento\Integration\Model\Integration'
        )->disableOriginalConstructor()->getMock();
        $mockIntegration->expects($this->any())->method('setData')->will($this->returnSelf());
        $mockIntegration->expects($this->any())->method('getData')->will($this->returnValue($data));
        $mockObjectManager->expects($this->any())->method('create')->will($this->returnValue($mockIntegration));
        /* @var \Magento\Integration\Model\Integration\Factory */
        $integrationFactory = new \Magento\Integration\Model\Integration\Factory($mockObjectManager);
        $integration = $integrationFactory->create($data);
        $this->assertEquals($data, $integration->getData(), 'The integration data is not set correctly');
    }
}
