# Magento_Signifyd module

## Overview

The Magento_Signifyd module provides integration with the [Signifyd](https://www.signifyd.com/) fraud protection system. The integration is based on the Signifyd API; see the [Signifyd API docs](https://www.signifyd.com/docs/api/#/introduction/) for technical details.

The module implementation allows to:

 - create a [Signifyd case](https://www.signifyd.com/docs/api/#/reference/cases) for a placed order
 - automatically receive a [Signifyd guarantee](https://www.signifyd.com/docs/api/#/reference/guarantees) for a created case
 - automatically cancel a guarantee when the order is canceled

## Extensibility

The Magento_Signifyd module does not add own Events, Layouts, and UI Components as extension points.

### Public API

The following interfaces (marked with the `@api` annotation) provide methods that allow to:

`Magento\Signifyd\Api\Data\CaseInterface` (common abstraction for the Signifyd case entity):

- set or retrieve all case data fields

`Magento\Signifyd\Api\CaseManagementInterface`:

- create a new case entity
- retrieve the existing case entity for a specified order

`Magento\Signifyd\Api\CaseCreationServiceInterface`:

- create a case entity for a specified order
- send a request through the Signifyd API to create a new case

`Magento\Signifyd\Api\CaseRepositoryInterface`:

- describe methods to work with a case entity

`Magento\Signifyd\Api\GuaranteeCreationServiceInterface`:

- send a request through the Signifyd API to create a new case guarantee

`Magento\Signifyd\Api\GuaranteeCancelingServiceInterface`:
- send a request through the Signifyd API to cancel the Signifyd case guarantee

`Magento\Signifyd\Api\Data\CaseSearchResultsInterface`:

- might be used by `Magento\Signifyd\Api\CaseRepositoryInterface` to retrieve a list of case entities by specific conditions

For information about a public API in Magento 2, see [Public interfaces & APIs](http://devdocs.magento.com/guides/v2.1/extension-dev-guide/api-concepts.html).

## Additional information

### Webhooks

To update the entity data for a case or guarantee, the Magento_Signifyd module uses the [Signifyd Webhooks](https://www.signifyd.com/docs/api/#/reference/webhooks) mechanism.

The newly created case entities have the `PENDING` status for a case and a guarantee. After receiving Webhook, both statuses are changed to appropriate Signifyd statuses.

### Debug mode

The Debug Mode may be enabled in the module configuration. This logs the communication data between the Magento_Signifyd module and the Signifyd service in this file:

    var/log/debug.log

### Backward incompatible changes

The Magento_Signifyd module does not introduce backward incompatible changes.

You can track [backward incompatible changes in patch releases](http://devdocs.magento.com/guides/v2.0/release-notes/changes/ee_changes.html).

### Processing supplementary payment information

To improve the accuracy of Signifyd's transaction estimation, you may perform these operations (links lead to the Magento Developer Documentation Portal):

- [Provide custom AVS/CVV mapping](http://devdocs.magento.com/guides/v2.2/payments-integrations/signifyd/signifyd.html#provide-avscvv-response-codes)

- [Retrieve payment method for a placed order](http://devdocs.magento.com/guides/v2.2/payments-integrations/signifyd/signifyd.html#retrieve-payment-method-for-a-placed-order)
