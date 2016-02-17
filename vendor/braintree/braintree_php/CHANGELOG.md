## 2.39.0
* Add oauth functionality
* Add 3DS info to the server side

## 2.38.0
* Update payment instrument types and test nonces
* Add missing valid params to PaymentMethodGateway

## 2.37.0
* Add 3D Secure transaction fields
* Add ability to create nonce from vaulted payment methods

## 2.36.0
* Surface Apple Pay payment instrument name in responses
* Support Coinbase payment instruments

## 2.35.2
* Fix E_STRICT errors
* Expose subscription status details

## 2.35.1
* Bugfix for auto loading files

## 2.35.0
* Allow PayPal fields in transaction.options.paypal
* Add error code constants
* Internal refactoring

## 2.34.0
* Add risk_data to Transaction and Verification with Kount decision and id
* Add verification_amount an option when creating a credit card
* Add TravelCruise industry type to Transaction
* Add room_rate to Lodging industry type
* Add CreditCard#verification as the latest verification on that credit card
* Add ApplePay support to all endpoints that may return ApplePayCard objects
* Add prefix to sample Webhook to simulate webhook query params

## 2.33.0
* Allow descriptor to be passed in Funding Details options params for Merchant Account create and update.

## 2.32.0
* Add additionalProcessorResponse to Transaction

## 2.31.1
* Allow payee_email to be passed in options params for Transaction create

## 2.31.0
* Added paypal specific fields to transaction calls               
* Added SettlementPending, SettlementDeclined transaction statuses

## 2.30.0
* Add descriptor url support

## 2.29.0
* Allow credit card verification options to be passed outside of the nonce for PaymentMethod.create
* Allow billing_address parameters and billing_address_id to be passed outside of the nonce for PaymentMethod.create
* Add Subscriptions to paypal accounts
* Add PaymentMethod.update
* Add fail_on_duplicate_payment_method option to PaymentMethod.create

## 2.28.0
* Adds support for v.zero SDKs.

## 2.27.2

* Make webhook parsing more robust with newlines
* Add messages to InvalidSignature exceptions

## 2.27.1

* Updated secureCompare to correctly compare strings in consistent time
* Add better error messages around webhook verification

## 2.27.0

* Include Dispute information on Transaction
* Search for Transactions disputed on a certain date

## 2.26.0

* Disbursement Webhooks

## 2.25.1

* Fix factories on AddOn and Discount (thanks [stewe](https://github.com/stewe))
* Allow billingAddressId on transaction create

## 2.25.0

* Merchant account find API

## 2.24.0

* Merchant account update API
* Merchant account create API v2

## 2.23.1

* Update configuration URLs

## 2.23.0

* Official Partnership support

## 2.22.2

* Add Partner Merchant Declined webhook
* use preg_callback_replace instead of preg_replace (thanks [jonthornton](https://github.com/jonthornton)!)

## 2.22.1

* Adds missing test contstant to library namespace

## 2.22.0

* Adds holdInEscrow method
* Add error codes for verification not supported error
* Add companyName and taxId to merchant account create
* Adds cancelRelease method
* Adds releaseFromEscrow functionality
* Adds phone to merchant account signature.
* Adds merchant account phone error code.
* Fix casing issues with Braintree\_Http and Braintree\_Util references (thanks [steven-hadfield](https://github.com/steven-hadfield)!)
* Fixed transaction initialization arguments to be optional (thanks [karolsojko](https://github.com/karolsojko)!)

## 2.21.0

* Enable device data.

## 2.20.0

* Fixed getting custom fields with valueForHtmlField. [Thanks to Miguel Manso for the fix.](https://github.com/mumia)
* Adds disbursement details to transactions.
* Adds image url to transactions.

## 2.19.0

* Adds channel field to transactions.

## 2.18.0

* Adds country of issuance and issuing bank bin database fields

## 2.17.0

* Adds verification search

## 2.16.0

* Additional card information, such as prepaid, debit, commercial, Durbin regulated, healthcare, and payroll, are returned on credit card responses
* Allows transactions to be specified as recurring

## 2.15.0

* Adds prepaid field to credit cards (possible values include Yes, No, Unknown)

## 2.14.1

* Adds composer support (thanks [till](https://github.com/till))
* Fixes erroneous version number
* Braintree_Plan::all() returns empty array if no plans exist

## 2.14.0

* Adds webhook gateways for parsing, verifying, and testing notifications

## 2.13.0

* Adds search for duplicate credit cards given a payment method token
* Adds flag to fail saving credit card to vault if card is duplicate

## 2.12.5

* Exposes plan_id on transactions

## 2.12.4

* Added error code for invalid purchase order number

## 2.12.3

* Fixed problematic case in ResourceCollection when no results are returned from a search.

## 2.12.2

* Fixed customer search, which returned customers when no customers matched search criteria

## 2.12.1

* Added new error message for merchant accounts that do not support refunds

## 2.12.0

* Added ability to retrieve all Plans, AddOns, and Discounts
* Added Transaction cloning

## 2.11.0

* Added Braintree_SettlementBatchSummary

## 2.10.1

* Wrap dependency requirement in a function, to prevent pollution of the global namespace

## 2.10.0

* Added subscriptionDetails to Transaction
* Added flag to store in vault only when a transaction is successful
* Added new error code

## 2.9.0

* Added a new transaction state, AUTHORIZATION_EXPIRED.
* Enabled searching by authorizationExpiredAt.

## 2.8.0

* Added next_billing_date and transaction_id to subscription search
* Added address_country_name to customer search
* Added new error codes

## 2.7.0

* Added Customer search
* Added dynamic descriptors to Subscriptions and Transactions
* Added level 2 fields to Transactions:
  * tax_amount
  * tax_exempt
  * purchase_order_number

## 2.6.1

* Added billingAddressId to allowed parameters for credit cards create and update
* Allow searching on subscriptions that are currently in a trial period using inTrialPeriod

## 2.6.0

* Added ability to perform multiple partial refunds on Braintree_Transactions
* Allow passing expirationMonth and expirationYear separately when creating Braintree_Transactions
* Added revertSubscriptionOnProrationFailure flag to Braintree_Subscription update that specifies how a Subscription should react to a failed proration charge
* Deprecated Braintree_Subscription nextBillAmount in favor of nextBillingPeriodAmount
* Deprecated Braintree_Transaction refundId in favor of refundIds
* Added new fields to Braintree_Subscription:
  * balance
  * paidThroughDate
  * nextBillingPeriodAmount

## 2.5.0

* Added Braintree_AddOns/Braintree_Discounts
* Enhanced Braintree_Subscription search
* Enhanced Braintree_Transaction search
* Added constants for Braintree_Result_CreditCardVerification statuses
* Added EXPIRED and PENDING statuses to Braintree_Subscription
* Allowed prorateCharges to be specified on Braintree_Subscription update
* Added Braintree_AddOn/Braintree_Discount details to Braintree_Transactions that were created from a Braintree_Subscription
* Removed 13 digit Visa Sandbox Credit Card number and replaced it with a 16 digit Visa
* Added new fields to Braintree_Subscription:
  * billingDayOfMonth
  * daysPastDue
  * firstBillingDate
  * neverExpires
  * numberOfBillingCycles

## 2.4.0

* Added ability to specify country using countryName, countryCodeAlpha2, countryCodeAlpha3, or countryCodeNumeric (see [ISO_3166-1](http://en.wikipedia.org/wiki/ISO_3166-1))
* Added gatewayRejectionReason to Braintree_Transaction and Braintree_Verification
* Added unified message to result objects

## 2.3.0

* Added unified Braintree_TransparentRedirect url and confirm methods and deprecated old methods
* Added functions to Braintree_CreditCard to allow searching on expiring and expired credit cards
* Allow card verification against a specified merchant account
* Added ability to update a customer, credit card, and billing address in one request
* Allow updating the paymentMethodToken on a subscription

## 2.2.0

* Prevent race condition when pulling back collection results -- search results represent the state of the data at the time the query was run
* Rename ResourceCollection's approximate_size to maximum_size because items that no longer match the query will not be returned in the result set
* Correctly handle HTTP error 426 (Upgrade Required) -- the error code is returned when your client library version is no long compatible with the gateway
* Add the ability to specify merchant_account_id when verifying credit cards
* Add subscription_id to transactions created from subscriptions

## 2.1.0

* Added transaction advanced search
* Added ability to partially refund transactions
* Added ability to manually retry past-due subscriptions
* Added new transaction error codes
* Allow merchant account to be specified when creating transactions
* Allow creating a transaction with a vault customer and new payment method
* Allow existing billing address to be updated when updating credit card
* Correctly handle xml with nil=true

## 2.0.0

* Updated success? on transaction responses to return false on declined transactions
* Search results now include Enumerable and will automatically paginate data
* Added credit_card[cardholder_name] to allowed transaction params and CreditCardDetails (thanks [chrismcc](http://github.com/chrismcc))
* Fixed a bug with Customer::all
* Added constants for error codes

## 1.2.1

* Added methods to get both shallow and deep errors from a Braintree_ValidationErrorCollection
* Added the ability to make a credit card the default card for a customer
* Added constants for transaction statuses
* Updated Quick Start in README.md to show a workflow with error checking

## 1.2.0

* Added subscription search
* Provide access to associated subscriptions from CreditCard
* Switched from using Zend framework for HTTP requests to using curl extension
* Fixed a bug in Transparent Redirect when arg_separator.output is configured as &amp; instead of &
* Increased http request timeout
* Fixed a bug where ForgedQueryString exception was being raised instead of DownForMaintenance
* Updated SSL CA files

## 1.1.1

* Added Braintree_Transaction::refund
* Added Braintree_Transaction::submitForSettlementNoValidate
* Fixed a bug in errors->onHtmlField when checking for errors on custom fields when there are none
* Added support for passing merchantAccountId for Transaction and Subscription

## 1.1.0

* Added recurring billing support

## 1.0.1

* Fixed bug with Braintree_Error_ErrorCollection.deepSize
* Added methods for accessing validation errors and params by html field name

## 1.0.0

* Initial release
