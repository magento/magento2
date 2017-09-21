<?php
/**
 * Created by PhpStorm.
 * User: jpolak
 * Date: 9/21/17
 * Time: 2:16 PM
 */

namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Customer\Model\Customer;
use Magento\OneTouchOrdering\Model\CustomerCardsFormatter;
use Magento\OneTouchOrdering\Model\CustomerCreditCardManager;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CustomerCardsFormatterTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cardToken;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerCreditCardManager;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Customer
     */
    private $customer;
    /**
     * @var CustomerCardsFormatter
     */
    private $customerCards;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->cardToken = $this->createMock(\Magento\Vault\Api\Data\PaymentTokenInterface::class);
        $this->customerCreditCardManager = $this->createMock(CustomerCreditCardManager::class);
        $this->customer = $this->createMock(Customer::class);
        $this->customerCards = $objectManager->getObject(
            CustomerCardsFormatter::class,
            [
                'customerCreditCardManager' => $this->customerCreditCardManager
            ]
        );
    }

    public function testGetFormattedCards()
    {
        $cardId = 2;
        $customerId = 321;
        $cardDetails = '{"type": "VI", "maskedCC": "1234", "expirationDate": "12/20"}';
        $cardFormatted = 'Type: Visa, ending: 1234 (expires: 12/20)';

        $this->customerCreditCardManager
            ->expects($this->once())
            ->method('getVisibleAvailableTokens')
            ->with($customerId)
            ->willReturn([$cardId => $this->cardToken]);

        $this->customer->expects($this->once())->method('getId')->willReturn($customerId);
        $this->cardToken->expects($this->once())->method('getTokenDetails')->willReturn($cardDetails);
        $this->cardToken->expects($this->once())->method('getEntityId')->willReturn($cardId);
        
        $result = $this->customerCards->getFormattedCards($this->customer);
        $this->assertSame($result[0]['card'], $cardFormatted);
        $this->assertSame($result[0]['id'], $cardId);
    }
}
