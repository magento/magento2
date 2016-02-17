<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';

class Braintree_MultipleValueNodeTest extends PHPUnit_Framework_TestCase
{
    function testIn_singleValue()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $activeSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '3'
        ))->subscription;

        $canceledSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '3'
        ))->subscription;
        Braintree_Subscription::cancel($canceledSubscription->id);

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::status()->in(array(Braintree_Subscription::ACTIVE)),
            Braintree_SubscriptionSearch::price()->is('3')
        ));
        foreach ($collection AS $item) {
            $this->assertEquals(Braintree_Subscription::ACTIVE, $item->status);
        }

        $this->assertTrue(Braintree_TestHelper::includes($collection, $activeSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $canceledSubscription));
    }

    function testIs()
    {
        $found = false;
        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::status()->is(Braintree_Subscription::PAST_DUE)
        ));
        foreach ($collection AS $item) {
            $found = true;
            $this->assertEquals(Braintree_Subscription::PAST_DUE, $item->status);
        }
        $this->assertTrue($found);
    }

    function testSearch_statusIsExpired()
    {
        $found = false;
        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::status()->in(array(Braintree_Subscription::EXPIRED))
        ));
        foreach ($collection AS $item) {
            $found = true;
            $this->assertEquals(Braintree_Subscription::EXPIRED, $item->status);
        }
        $this->assertTrue($found);
    }

    function testIn_multipleValues()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $activeSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '4'
        ))->subscription;

        $canceledSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '4'
        ))->subscription;
        Braintree_Subscription::cancel($canceledSubscription->id);

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::status()->in(array(Braintree_Subscription::ACTIVE, Braintree_Subscription::CANCELED)),
            Braintree_SubscriptionSearch::price()->is('4')
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $activeSubscription));
        $this->assertTrue(Braintree_TestHelper::includes($collection, $canceledSubscription));
    }
}
