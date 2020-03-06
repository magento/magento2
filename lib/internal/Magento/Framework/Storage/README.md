The Storage library provides abstraction over different file storage providers.

## Usage

A module that needs file storage, it can be configured via `\Magento\Framework\Storage\StorageProvider` in `di.xml`:

```xml
<type name="Magento\Framework\Storage\StorageProvider">
    <arguments>
        <argument name="storage" xsi:type="array">
            <item name="storage-name" xsi:type="string">default/location</item>
        </argument>
    </arguments>
</type>
```

`default/location` is a default path in local filesystem, relative to Magento root.

Now, in a PHP class that uses the declared storage, use the same `\Magento\Framework\Storage\StorageProvider` to get it:

```php
/**
 * @var \Magento\Framework\Storage\StorageProvider
 */
private $storageProvider;

public function doSomething()
{
    $storage = $this->storageProvider->get('storage-name')
    $storage->put('path.txt', $content);
}
```

## Configuring Storage

A storage can be configured in `env.php`:

```php
'storage' => [
    'storage-name' => [
        'adapter' => 'aws_s3',
        'options' => [
            'client' => [
                'credentials' => [
                    'key'    => '<key>',
                    'secret' => '<secret>'
                ],
                'region' => '<region>',
                'version' => 'latest',
            ],
            'bucket' => '<bucket>',
        ],
    ],
    'media' => [
        // this is default configuration, so it doesn't need to be configured explicitly like so
        'adapter' => 'local',
        'options' => [
            'root' => 'pub/media'
        ]
    ]
]
```

Different providers have different `options` available for configuration.
Under the hood, Magento Storage relies on [Flysystem](https://github.com/thephpleague/flysystem) library, so`options` might reflect options required by a corresponding storage adapter implemented for Flysystem.  

## Storage Providers

By default, Magento Storage provides support for the following storage providers:

* Local filesystem (based on `\League\Flysystem\Adapter\Local`)
   * Adapter name: `local`
   * Options: 
      ```php
        [
            'root' => 'path/relative/to/magento/root'
        ]
      ```
* AWS S3 V3 (based on `\League\Flysystem\AwsS3v3\AwsS3Adapter`)
   * Adapter name: `aws_s3`
   * Options:
       ```php
          [
              'client' => [
                  'credentials' => [
                      'key'    => '<key>',
                      'secret' => '<secret>'
                  ],
                  'region' => '<region>',
                  'version' => 'latest',
              ],
              'bucket' => '<bucket>',
              'prefix' => '<prefix>',
          ]
        ```
* Azure Blob storage (based on `\League\Flysystem\AzureBlobStorage\AzureBlobStorageAdapter`)
   * Adapter name: `ms_azure`
   * Options:
        ```php
          [
              'connection_string' => '<connection-string>',
              'container_name' => '<container-name>',
              'prefix' => '<prefix>',
          ]
        ```

Additional adapters can be added by:
1. Creating an adapter factory implementing `\Magento\Framework\Storage\AdapterFactory\AdapterFactoryInterface`
2. Registering the factory in `Magento\Framework\Storage\StorageProvider` via `di.xml`:     
    ```xml
        <type name="Magento\Framework\Storage\StorageProvider">
            <arguments>
                <argument name="storageAdapters" xsi:type="array">
                    <item name="custom_adapter" xsi:type="string">My\Storage\AdapterFactory</item>
                </argument>
            </arguments>
        </type>
    ```

The factory is registered as a "string" (name of the class).
That's because in most cases only a few adapters will be really created for a single application, and we don't want to create unnecessary factory instances. 
