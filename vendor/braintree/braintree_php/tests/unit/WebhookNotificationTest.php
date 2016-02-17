<?php
require_once realpath(dirname(__FILE__)) . '/../TestHelper.php';

class Braintree_WebhookNotificationTest extends PHPUnit_Framework_TestCase
{
    function setup()
    {
        integrationMerchantConfig();
    }

    function testVerify()
    {
        $verificationString = Braintree_WebhookNotification::verify('verification_token');
        $this->assertEquals('integration_public_key|c9f15b74b0d98635cd182c51e2703cffa83388c3', $verificationString);
    }

    function testSampleNotificationReturnsAParsableNotification()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE, $webhookNotification->kind);
        $this->assertNotNull($webhookNotification->timestamp);
        $this->assertEquals("my_id", $webhookNotification->subscription->id);
    }

    function testParsingModifiedSignatureRaisesError()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree_Exception_InvalidSignature', 'signature does not match payload - one has been modified');

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'] . "bad",
            $sampleNotification['bt_payload']
        );
    }

    function testParsingWebhookWithWrongKeysRaisesError()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        Braintree_Configuration::environment('development');
        Braintree_Configuration::merchantId('integration_merchant_id');
        Braintree_Configuration::publicKey('wrong_public_key');
        Braintree_Configuration::privateKey('wrong_private_key');

        $this->setExpectedException('Braintree_Exception_InvalidSignature', 'no matching public key');

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "bad" . $sampleNotification['bt_payload']
        );
    }

    function testParsingModifiedPayloadRaisesError()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree_Exception_InvalidSignature');

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "bad" . $sampleNotification['bt_payload']
        );
    }

    function testParsingUnknownPublicKeyRaisesError()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree_Exception_InvalidSignature');

        $webhookNotification = Braintree_WebhookNotification::parse(
            "bad" . $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );
    }

    function testParsingInvalidSignatureRaisesError()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree_Exception_InvalidSignature');

        $webhookNotification = Braintree_WebhookNotification::parse(
            "bad_signature",
            $sampleNotification['bt_payload']
        );
    }

    function testParsingInvalidCharactersRaisesError()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree_Exception_InvalidSignature', 'payload contains illegal characters');

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "~*~*invalid*~*~"
        );
    }

    function testParsingAllowsAllValidCharacters()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $this->setExpectedException('Braintree_Exception_InvalidSignature', 'signature does not match payload - one has been modified');

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+=/\n"
        );
    }

    function testParsingRetriesPayloadWithANewline()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE,
            'my_id'
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            rtrim($sampleNotification['bt_payload'])
        );
    }

    function testBuildsASampleNotificationForAMerchantAccountApprovedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_APPROVED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->merchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_ACTIVE, $webhookNotification->merchantAccount->status);
        $this->assertEquals("master_ma_for_my_id", $webhookNotification->merchantAccount->masterMerchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_ACTIVE, $webhookNotification->merchantAccount->masterMerchantAccount->status);
    }

    function testBuildsASampleNotificationForAMerchantAccountDeclinedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::SUB_MERCHANT_ACCOUNT_DECLINED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->merchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_SUSPENDED, $webhookNotification->merchantAccount->status);
        $this->assertEquals("master_ma_for_my_id", $webhookNotification->merchantAccount->masterMerchantAccount->id);
        $this->assertEquals(Braintree_MerchantAccount::STATUS_SUSPENDED, $webhookNotification->merchantAccount->masterMerchantAccount->status);
        $this->assertEquals("Credit score is too low", $webhookNotification->message);
        $errors = $webhookNotification->errors->forKey('merchantAccount')->onAttribute('base');
        $this->assertEquals(Braintree_Error_Codes::MERCHANT_ACCOUNT_DECLINED_OFAC, $errors[0]->code);
    }

    function testBuildsASampleNotificationForATransactionDisbursedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::TRANSACTION_DISBURSED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::TRANSACTION_DISBURSED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->transaction->id);
        $this->assertEquals(100, $webhookNotification->transaction->amount);
        $this->assertNotNull($webhookNotification->transaction->disbursementDetails->disbursementDate);
    }

    function testBuildsASampleNotificationForADisputeOpenedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::DISPUTE_OPENED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::DISPUTE_OPENED, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->dispute->id);
        $this->assertEquals(Braintree_Dispute::OPEN, $webhookNotification->dispute->status);
    }

    function testBuildsASampleNotificationForADisputeLostWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::DISPUTE_LOST,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::DISPUTE_LOST, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->dispute->id);
        $this->assertEquals(Braintree_Dispute::LOST, $webhookNotification->dispute->status);
    }

    function testBuildsASampleNotificationForADisputeWonWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::DISPUTE_WON,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::DISPUTE_WON, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->dispute->id);
        $this->assertEquals(Braintree_Dispute::WON, $webhookNotification->dispute->status);
    }

    function testBuildsASampleNotificationForADisbursementExceptionWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::DISBURSEMENT_EXCEPTION,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );


        $this->assertEquals(Braintree_WebhookNotification::DISBURSEMENT_EXCEPTION, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->disbursement->id);
        $this->assertEquals(false, $webhookNotification->disbursement->retry);
        $this->assertEquals(false, $webhookNotification->disbursement->success);
        $this->assertEquals("bank_rejected", $webhookNotification->disbursement->exceptionMessage);
        $this->assertEquals(100.00, $webhookNotification->disbursement->amount);
        $this->assertEquals("update_funding_information", $webhookNotification->disbursement->followUpAction);
        $this->assertEquals("merchant_account_token", $webhookNotification->disbursement->merchantAccount->id);
        $this->assertEquals(new DateTime("2014-02-10"), $webhookNotification->disbursement->disbursementDate);
        $this->assertEquals(array("asdfg", "qwert"), $webhookNotification->disbursement->transactionIds);
    }

    function testBuildsASampleNotificationForADisbursementWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::DISBURSEMENT,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );


        $this->assertEquals(Braintree_WebhookNotification::DISBURSEMENT, $webhookNotification->kind);
        $this->assertEquals("my_id", $webhookNotification->disbursement->id);
        $this->assertEquals(false, $webhookNotification->disbursement->retry);
        $this->assertEquals(true, $webhookNotification->disbursement->success);
        $this->assertEquals(NULL, $webhookNotification->disbursement->exceptionMessage);
        $this->assertEquals(100.00, $webhookNotification->disbursement->amount);
        $this->assertEquals(NULL, $webhookNotification->disbursement->followUpAction);
        $this->assertEquals("merchant_account_token", $webhookNotification->disbursement->merchantAccount->id);
        $this->assertEquals(new DateTime("2014-02-10"), $webhookNotification->disbursement->disbursementDate);
        $this->assertEquals(array("asdfg", "qwert"), $webhookNotification->disbursement->transactionIds);
    }
    function testBuildsASampleNotificationForAPartnerMerchantConnectedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::PARTNER_MERCHANT_CONNECTED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::PARTNER_MERCHANT_CONNECTED, $webhookNotification->kind);
        $this->assertEquals("public_id", $webhookNotification->partnerMerchant->merchantPublicId);
        $this->assertEquals("public_key", $webhookNotification->partnerMerchant->publicKey);
        $this->assertEquals("private_key", $webhookNotification->partnerMerchant->privateKey);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
        $this->assertEquals("cse_key", $webhookNotification->partnerMerchant->clientSideEncryptionKey);
    }

    function testBuildsASampleNotificationForAPartnerMerchantDisconnectedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::PARTNER_MERCHANT_DISCONNECTED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::PARTNER_MERCHANT_DISCONNECTED, $webhookNotification->kind);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
    }

    function testBuildsASampleNotificationForAPartnerMerchantDeclinedWebhook()
    {
        $sampleNotification = Braintree_WebhookTesting::sampleNotification(
            Braintree_WebhookNotification::PARTNER_MERCHANT_DECLINED,
            "my_id"
        );

        $webhookNotification = Braintree_WebhookNotification::parse(
            $sampleNotification['bt_signature'],
            $sampleNotification['bt_payload']
        );

        $this->assertEquals(Braintree_WebhookNotification::PARTNER_MERCHANT_DECLINED, $webhookNotification->kind);
        $this->assertEquals("abc123", $webhookNotification->partnerMerchant->partnerMerchantId);
    }
}
