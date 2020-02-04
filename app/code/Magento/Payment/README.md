The Magento_Payment module provides the abstraction level for all payment methods, and all logic that should be used when adding a new payment method. This logic includes configuration models, separate models for payment data verification and so on.
For example, Magento\Payment\Model\Method\AbstractMethod is an abstract model which should be extended by particular payment methods.
