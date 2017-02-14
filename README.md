The Magento_Signifyd module provides integration with [Signifyd](https://www.signifyd.com/) fraud protection tool.

#### Introduction

Current implementation allows to create a [case](https://www.signifyd.com/docs/api/#/reference/cases) for the placed order,
automatically retrieves [guarantee](https://www.signifyd.com/docs/api/#/reference/guarantees) for a created case and
can cancel Signifyd guarantee on the order canceling.

Magento integration uses Signifyd API and all needed technical details can be found in the [Signifyd API docs](https://www.signifyd.com/docs/api/#/introduction/).

Magento_Signifyd module has extension points for 3d-party developers marked with `@api` annotation:

 - `CaseInterface` - common absraction for a Signifyd case entity, provides methods to set or retrieve all case data fields.
 - `CaseManagementInterface` - contains methods to create new case entity or retrieve existing for a specified order.
 - `CaseCreationServiceInterface` - provides an ability to create case entity for a specified order and send request thru Signifyd API to create a new case.
 - `CaseRepositoryInterface` - describes methods to work with case entity.
 - `GuaranteeCreationServiceInterface` - allows to send request thru Signifyd API to create new case guarantee.
 - `GuaranteeCancelingServiceInterface` - allows to send request thru Signifyd API to cancel Signifyd case guarantee.
 - `CaseSearchResultsInterface` - might be used by `CaseRepositoryInterface` to retrieve list of case entities by specific
conditions.

To update case(guarantee) entity data Magento implementation uses [Signifyd Webhooks](https://www.signifyd.com/docs/api/#/reference/webhooks) mechanism.
New created case entity will have `PENDING` status for a case and guarantee. After receiving Webhook, both statuses will be changed to appropriate Signifyd statuses.

#### Customization

Signifyd service collects a lof of different information related for order (all fields described in [API](https://www.signifyd.com/docs/api/#/reference/cases/create-a-case)),
most of these fields are optional but some of them are required (like `avsResponseCode`, `cvvResponseCode`),
so, for more accurate calculations,3d party integrations, like payment methods, might provide some additional details, like CVV/AVS response codes.

The 3d party payment methods can implement `\Magento\Payment\Api\PaymentVerificationInterface` to provide AVS/CVV mapping 
from specific codes to [EMS standard](http://www.emsecommerce.net/avs_cvv2_response_codes.htm) and register these mappers in custom payment module `condig.xml` file.
For example, the mappers registration might look similar to the next:

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

Described steps are enough to provide custom AVS/CVV mapping for custom payment integrations, everything else, like mapper initialization,
will be provided by Magento Signifyd infrastructure.