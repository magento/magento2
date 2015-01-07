<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Model;

class AdminAccountFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $serviceLocatorMock = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', ['get']);
        $serviceLocatorMock
            ->expects($this->once())
            ->method('get')
            ->with('Magento\Framework\Math\Random')
            ->will(
                $this->returnValue(
                    $this->getMockBuilder('Magento\Framework\Math\Random')
                        ->disableOriginalConstructor()
                        ->getMock()
                )
            );
        $adminAccountFactory = new AdminAccountFactory($serviceLocatorMock);
        $adminAccount = $adminAccountFactory->create(
            $this->getMockBuilder('Magento\Setup\Module\Setup')->disableOriginalConstructor()->getMock(),
            []
        );
        $this->assertInstanceOf('Magento\Setup\Model\AdminAccount', $adminAccount);
    }
}
