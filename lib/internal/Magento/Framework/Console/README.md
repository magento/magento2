# Console

This component contains Magento Cli and can be extended via DI configuration.

For example we can introduce new command in module using di.xml:

```
<type name="Magento\Framework\Console\CommandListInterface">
    <arguments>
        <argument name="commands" xsi:type="array">
            <item name="test_me" xsi:type="object">Magento\MyModule\Console\TestMeCommand</item>
        </argument>
    </arguments>
</type>
```

