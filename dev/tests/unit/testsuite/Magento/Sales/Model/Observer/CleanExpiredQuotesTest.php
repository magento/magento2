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
namespace Magento\Sales\Model\Observer;

/**
 * Tests Magento\Sales\Model\Observer\CleanExpiredQuotes
 */
class CleanExpiredQuotesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\StoresConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storesConfigMock;

    /**
     * @var \Magento\Sales\Model\Resource\Quote\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteFactoryMock;

    /**
     * @var \Magento\Sales\Model\Observer\CleanExpiredQuotes
     */
    protected $observer;

    protected function setUp()
    {
        $this->storesConfigMock = $this->getMock('Magento\Store\Model\StoresConfig', [], [], '', false);

        $this->quoteFactoryMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Quote\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->observer = new CleanExpiredQuotes($this->storesConfigMock, $this->quoteFactoryMock);
    }

    /**
     * @param array $lifetimes
     * @param array $additionalFilterFields
     * @dataProvider cleanExpiredQuotesDataProvider
     */
    public function testExecute($lifetimes, $additionalFilterFields)
    {
        $this->storesConfigMock->expects($this->once())
            ->method('getStoresConfigByPath')
            ->with($this->equalTo('checkout/cart/delete_quote_after'))
            ->will($this->returnValue($lifetimes));

        $quotesMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Quote\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteFactoryMock->expects($this->exactly(count($lifetimes)))
            ->method('create')
            ->will($this->returnValue($quotesMock));
        $quotesMock->expects($this->exactly((3 + count($additionalFilterFields)) * count($lifetimes)))
            ->method('addFieldToFilter');
        if (!empty($lifetimes)) {
            $quotesMock->expects($this->exactly(count($lifetimes)))
                ->method('walk')
                ->with('delete');
        }
        $this->observer->setExpireQuotesAdditionalFilterFields($additionalFilterFields);
        $this->observer->execute();
    }

    public function cleanExpiredQuotesDataProvider()
    {
        return [
            [[], []],
            [[1 => 100, 2 => 200], []],
            [[1 => 100, 2 => 200], ['field1' => 'condition1', 'field2' => 'condition2']],
        ];
    }
}
