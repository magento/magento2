# Logger

**Logger** provides a standard mechanism to log to system and error logs.

## Reporting exceptions

Pass the `Throwable` in the PSR-3 reserved `exception` context key, and keep the message a
constant template so that occurrences of the same fault aggregate together:

```php
$this->logger->critical('Unable to process image for product {productId}', [
    'productId' => $productId,
    'exception' => $e,
]);
```

Do not stringify the exception into the message (`$this->logger->critical($e)`) — the trace ends
up in the message field as free text, the message becomes unique per occurrence, and the record
is not routed to `exception.log`. Do not put `$e->getTrace()` in the context either: trace frames
carry call arguments, which may contain personal data or credentials.

## Log formatting

`Magento\Framework\Logger\Handler\Base` formats records with a Monolog `LineFormatter` that
includes stack traces. A different formatter can be injected without extending the handler, for
example to emit one valid JSON document per record for log aggregation:

```xml
<virtualType name="jsonLogFormatter" type="Monolog\Formatter\JsonFormatter">
    <arguments>
        <argument name="includeStacktraces" xsi:type="boolean">true</argument>
    </arguments>
</virtualType>
<type name="Magento\Framework\Logger\Handler\System">
    <arguments>
        <argument name="formatter" xsi:type="object">jsonLogFormatter</argument>
    </arguments>
</type>
<type name="Magento\Framework\Logger\Handler\Exception">
    <arguments>
        <argument name="formatter" xsi:type="object">jsonLogFormatter</argument>
    </arguments>
</type>
```

Each handler owns its formatter, so `system.log` and `exception.log` have to be configured
separately — `Handler\System` delegates records that carry `context['exception']` to
`Handler\Exception`, which formats them itself.
