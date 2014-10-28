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
namespace Magento\Sales\Service\V1\Data;

/**
 * Class CreditmemoConverterTest
 */
class CreditmemoConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Service\V1\Data\CreditmemoConverter
     */
    protected $converter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $loaderMock;

    public function setUp()
    {
        $this->loaderMock = $this->getMockBuilder('Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->converter = new \Magento\Sales\Service\V1\Data\CreditmemoConverter($this->loaderMock);
    }

    public function testGetModel()
    {
        $itemMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\CreditmemoItem')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $items = [$itemMock];

        $dataObjectMock = $this->getMockBuilder('Magento\Sales\Service\V1\Data\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $dataObjectMock->expects($this->once())
            ->method('getOrderId')
            ->willReturn(1);
        $dataObjectMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn(1);
        $dataObjectMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $creditmemoMock = $this->getMockBuilder('Magento\Sales\Model\Order\Creditmemo')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->loaderMock->expects($this->once())
            ->method('load')
            ->willReturn($creditmemoMock);
        $this->assertInstanceOf('Magento\Sales\Model\Order\Creditmemo', $this->converter->getModel($dataObjectMock));
    }
}
