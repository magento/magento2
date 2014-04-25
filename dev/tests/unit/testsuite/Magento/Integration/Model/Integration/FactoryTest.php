<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Integration\Model\Integration;

use Magento\Integration\Block\Adminhtml\Integration\Edit\Tab\Info;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject */
        $mockObjectManager = $this->getMockBuilder('Magento\Framework\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $data = array(
            Info::DATA_NAME => 'nameTest',
            Info::DATA_ID => '1',
            Info::DATA_EMAIL => 'test@magento.com',
            Info::DATA_ENDPOINT => 'http://magento.ll/endpoint'
        );
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
