# The Magento Signifyd module overview

The Magento_Signifyd module provides integration with the [Signifyd](https://www.signifyd.com/) fraud protection tool.

## Introduction

The module implementation allows to:

 - create the Signifyd [case](https://www.signifyd.com/docs/api/#/reference/cases) for a placed order
 - automatically receive the Signifyd [guarantee](https://www.signifyd.com/docs/api/#/reference/guarantees) for a created case
 - automatically cancel a guarantee when the order is canceled.

Magento integration uses the Signifyd API; see the [Signifyd API docs](https://www.signifyd.com/docs/api/#/introduction/) for technical details.

For external developers, the module contains these extension points (marked with `@api` annotation):

 - `CaseInterface` - common absraction for the Signifyd case entity, provides methods to set or retrieve all case data fields.
 - `CaseManagementInterface` - contains methods to create a new case entity or retrieve existing for a specified order.
 - `CaseCreationServiceInterface` - provides an ability to create case entity for a specified order and send request through the Signifyd API to create a new case.
 - `CaseRepositoryInterface` - describes methods to work with a case entity.
 - `GuaranteeCreationServiceInterface` - allows to send request through the Signifyd API to create a new case guarantee.
 - `GuaranteeCancelingServiceInterface` - allows to send request through the Signifyd API to cancel the Signifyd case guarantee.
 - `CaseSearchResultsInterface` - might be used by `CaseRepositoryInterface` to retrieve a list of case entities by specific
conditions.

To update entity data for a case or guarantee this module uses the [Signifyd Webhooks](https://www.signifyd.com/docs/api/#/reference/webhooks) mechanism.
Newly created case entity will have `PENDING` status for a case and guarantee. After receiving Webhook, both statuses will be changed to appropriate Signifyd statuses.

## Customization

The Signifyd service collects a lot of information about an order (all fields described in [API](https://www.signifyd.com/docs/api/#/reference/cases/create-a-case)),
most of these fields are optional but some of them are required (like `avsResponseCode`, `cvvResponseCode`).
So, for more accurate calculations, external integrations, like payment methods, might provide some additional details, like CVV/AVS response codes.

The custom payment methods can implement `\Magento\Payment\Api\PaymentVerificationInterface` to provide AVS/CVV mapping 
from specific codes to [EMS standard](http://www.emsecommerce.net/avs_cvv2_response_codes.htm) and register these mappers in the `condig.xml` file
of a custom payment module.
For example, the mappers registration might look like this:

```xml
<default>
    <payment>
        <custom_payment>
            <model>CustomPaymentFacade</model>
            <title>Custom Payment</title>
            ...
            <avs_ems_adapter>Magento\CustomPayment\Model\AvsEmsCodeMapper</avs_ems_adapter>
            <cvv_ems_adapter>Magento\CustomPayment\Model\CvvEmsCodeMapper</cvv_ems_adapter>
        </custom_payment>
    </payment>
</default>
```

These steps are enough to provide custom AVS/CVV mapping for payment integrations, everything else, like mapper initialization,
will be provided by the Magento Signifyd infrastructure.

Also, Signifyd can retrieve payment method for a placed order (the Magento Signifyd module can map Magento and Signifyd
payment codes using the predefined XML list, located in `Magento\Signifyd\etc\signifyd_payment_mapping.xml` file).
The 3rd-party payment integrations can apply own mappings for the [Signifyd payment codes](https://www.signifyd.com/docs/api/#/reference/cases/create-a-case),
it's enough to add `signifyd_payment_mapping.xml` to custom payment method implementation and specify needed mapping.
For example:

```xml
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Signifyd:etc/signifyd_payment_mapping.xsd">
    <payment_method_list>
        <payment_method>
            <magento_code>custom_payment_code</magento_code>
            <signifyd_code>PAYMENT_CARD</signifyd_code>
        </payment_method>
    </payment_method_list>
</config>    
```

 - `magento_code` attribute value should be the code for a custom payment method (the same as in the payment's `config.xml`).
 - `signifyd_code` attribute value should be one of available the Signifyd payment method codes.