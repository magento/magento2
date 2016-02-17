<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';
require_once realpath(dirname(__FILE__)) . '/SubscriptionTestHelper.php';

class Braintree_TextNodeTest extends PHPUnit_Framework_TestCase
{
    function testIs()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id'],
            'price' => '5'
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '5'
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->is("integration_trial_plan"),
            Braintree_SubscriptionSearch::price()->is('5')
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $triallessSubscription));
    }

    function testIsNot()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id'],
            'price' => '6'
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '6'
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->isNot("integration_trialless_plan"),
            Braintree_SubscriptionSearch::price()->is("6")
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $triallessSubscription));
    }

    function testStartsWith()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id'],
            'price' => '7'
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '7'
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->startsWith("integration_trial_pl"),
            Braintree_SubscriptionSearch::price()->is("7")
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $triallessSubscription));
    }

    function testEndsWith()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id'],
            'price' => '8'
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '8'
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->endsWith("rial_plan"),
            Braintree_SubscriptionSearch::price()->is("8")
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $triallessSubscription));
    }


    function testContains()
    {
        $creditCard = Braintree_SubscriptionTestHelper::createCreditCard();
        $triallessPlan = Braintree_SubscriptionTestHelper::triallessPlan();
        $trialPlan = Braintree_SubscriptionTestHelper::trialPlan();

        $trialSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $trialPlan['id'],
            'price' => '9'
        ))->subscription;

        $triallessSubscription = Braintree_Subscription::create(array(
            'paymentMethodToken' => $creditCard->token,
            'planId' => $triallessPlan['id'],
            'price' => '9'
        ))->subscription;

        $collection = Braintree_Subscription::search(array(
            Braintree_SubscriptionSearch::planId()->contains("ration_trial_pl"),
            Braintree_SubscriptionSearch::price()->is("9")
        ));

        $this->assertTrue(Braintree_TestHelper::includes($collection, $trialSubscription));
        $this->assertFalse(Braintree_TestHelper::includes($collection, $triallessSubscription));
    }
}
