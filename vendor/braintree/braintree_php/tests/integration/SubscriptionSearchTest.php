<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';

class Braintree_SubscriptionSearchTest extends PHPUnit_Framework_TestCase
{
    function testSearch_planIdIs()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id'],
            'price' => '1'
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '1'
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->is('integration_trial_plan'),
            Braintree_SubscriptionSearch::price()->is('1')
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $triallessSubscription));
    }

    function test_noRequestsWhenIterating()
    {
        $resultsReturned = false;
        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->is('imaginary')
        ));

        foreach($collection as $transaction) {
            $resultsReturned = true;
            break;
        }

        $this->assertSame(0, $collection->maximumCount());
        $this->assertEquals(false, $resultsReturned);
    }

    function testSearch_inTrialPeriod()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id'],
            'price' => '1'
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '1'
        ))->subscription;

        $subscriptions_in_trial = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::inTrialPeriod()->is(true)
        ));

        $this->assertTrue(Braintree_TestHelper::includes($subscriptions_in_trial, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($subscriptions_in_trial, $triallessSubscription));

        $subscriptions_not_in_trial = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::inTrialPeriod()->is(false)
        ));

        $this->assertTrue(Braintree_TestHelper::includes($subscriptions_not_in_trial, $triallessSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($subscriptions_not_in_trial, $trialSubscription));
    }

    function testSearch_statusIsPastDue()
    {
        $found = false;
        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::status()->in(array(Braintree_Subscription::PAST_DUE))
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

    function testSearch_billingCyclesRemaing()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $subscription_4 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'numberOfBillingCycles' => 4
        ))->subscription;

        $subscription_8 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'numberOfBillingCycles' => 8
        ))->subscription;

        $subscription_10 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'numberOfBillingCycles' => 10
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::billingCyclesRemaining()->between(5, 10)
        ));

        $this->assertFalse(Braintree_TestHelper::includes($collection, $subscription_4));
        $this->assertTrue(Braintree_TestHelper::includes($collection, $subscription_8));
        $this->assertTrue(Braintree_TestHelper::includes($collection, $subscription_10));
    }

    function testSearch_subscriptionId()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $rand_id = strval(rand());

        $subscription_1 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'id' => 'subscription_123_id_' . $rand_id
        ))->subscription;

        $subscription_2 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'id' => 'subscription_23_id_' . $rand_id
        ))->subscription;

        $subscription_3 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'id' => 'subscription_3_id_' . $rand_id
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::id()->contains("23_id_")
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $subscription_1));
        $this->assertTrue(Braintree_TestHelper::includes($collection, $subscription_2));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $subscription_3));
    }

    function testSearch_merchantAccountId()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $rand_id = strval(rand());

        $subscription_1 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'id' => strval(rand()) . '_subscription_' . $rand_id,
            'price' => '2'
        ))->subscription;

        $subscription_2 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'id' => strval(rand()) . '_subscription_' . $rand_id,
            'merchantAccountId' => Braintree_TestHelper::nonDefaultMerchantAccountId(),
            'price' => '2'
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::id()->endsWith('subscription_' . $rand_id),
            Braintree_SubscriptionSearch::merchantAccountId()->in(array(Braintree_TestHelper::nonDefaultMerchantAccountId())),
            Braintree_SubscriptionSearch::price()->is('2')
        ));

        $this->assertFalse(Braintree_TestHelper::includes($collection, $subscription_1));
        $this->assertTrue(Braintree_TestHelper::includes($collection, $subscription_2));
    }

    function testSearch_bogusMerchantAccountId()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $rand_id = strval(rand());

        $subscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'id' => strval(rand()) . '_subscription_' . $rand_id,
            'price' => '11.38'
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::id()->endsWith('subscription_' . $rand_id),
            Braintree_SubscriptionSearch::merchantAccountId()->in(array("bogus_merchant_account")),
            Braintree_SubscriptionSearch::price()->is('11.38')
        ));

        $this->assertFalse(Braintree_TestHelper::includes($collection, $subscription));
    }

    function testSearch_daysPastDue()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $subscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id']
        ))->subscription;

        $http = new Braintree_Http(Braintree_Configuration::$global);
        $path = Braintree_Configuration::$global->merchantPath() . '/subscriptions/' . $subscription->id . '/make_past_due';
        $http->put($path, array('daysPastDue' => 5));

        $found = false;
        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::daysPastDue()->between(2, 10)
        ));
        foreach ($collection AS $item) {
            $found = true;
            $this->assertTrue($item->daysPastDue <= 10);
            $this->assertTrue($item->daysPastDue >= 2);
        }
        $this->assertTrue($found);
    }

    function testSearch_price()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $subscription_850 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '8.50'
        ))->subscription;

        $subscription_851 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '8.51'
        ))->subscription;

        $subscription_852 = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '8.52'
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::price()->between('8.51', '8.52')
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $subscription_851));
        $this->assertTrue(Braintree_TestHelper::includes($collection, $subscription_852));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $subscription_850));
    }

    function testSearch_nextBillingDate()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
        ))->subscription;

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id'],
        ))->subscription;

        $fiveDaysFromNow = new DateTime();
        $fiveDaysFromNow->modify("+5 days");

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::nextBillingDate()->greaterThanOrEqualTo($fiveDaysFromNow)
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $triallessSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $trialSubscription));
    }

    function testSearch_transactionId()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();

        $matchingSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
        ))->subscription;

        $nonMatchingSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::transactionId()->is($matchingSubscription->transactions[0]->id)
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $matchingSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $nonMatchingSubscription));
    }
}
