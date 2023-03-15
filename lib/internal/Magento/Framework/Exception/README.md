# Exception

* Classes that extend the base Exception class represent types of Exceptions.
* All Exception classes extend, directly or indirectly, LocalizedException. Thus, error messages can be localized.
* Several Exception classes extend, directly or indirectly, AbstractAggregateException. This allows them to store ErrorMessage objects.
* The ErrorMessage class stores and renders localized error information.
