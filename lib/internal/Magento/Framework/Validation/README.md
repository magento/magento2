Validation Library supporting multiple validation errors returned.

ValidationResult object supposed to be created by dedicated validator service which makes a validation and checks
whether all entity invariants (business rules that always should be fulfilled) are valid.

ValidationResult represents a container storing all the validation errors that happened during the entity validation.

ValidationException exists to be compatible with the Web-API (SOAP and REST) implementation which currently
uses Magento\Framework\Exception\AggregateExceptionInterface returned as a result of ServiceContracts call
to support Multi-Error response.
