# Overview

Module Magento_CatalogStorefront provides Catalog Storefront service implementation and has the following responsibilities:

- Provide product/category data by specified ids and set of attributes

Here is the example of request and response to Product Service
In case of data is not found for specified id Service doesn't return any data
```
// Request
{
    "ids": [2, 6, 20],
    "scopes": {
        "store": 1,
        "customerGroupId": 1
    },
    "attributes": [
        "name",
        "price"
    ]
}

// Response
{
    "items": [
        {
            "id": 2,
            "name": "Car 1",
            "price": "22"
        },
        {
            "id": 6,
            "name": "Car 2",
            "price": "21"
        },
    ],
    "errors": null
}
```


## Storage

Storage API depicts the interface of the collaboration between Catalog Storefront application
and the Storage service behind them. Currently, there is only Elasticsearch implementation.
Elasticsearch was chosen as a storage service by default because it's the most reliable
and easy scalable document oriented storage service that is fit under our performance 
expectations from the Catalog Storefront application itself.

Storage API split into three different piece:
1. DataDefinitionInterface - DDL operations with storage. For more details and to see what's
operations are required for Catalog Storefront application, please look at the interface.
2. CommandInterface - contains write operations that is required for Catalog Storefront 
application.
3. QueryInterface - contains data retrieving operations from the storage.

To be able to replace current implementation you need to implement three interfaces
above and override DI preferences to new implementations.

Default Storage configuration:
(You can override any options through app/etc/env.php file.)
```
    'catalog-store-front' => [
        'connections' => [
            'default' => [
                'protocol' => 'http',
                'hostname' => 'localhost',
                'port' => '9200',
                'username' => '',
                'password' => '',
                'timeout' => 3
            ]
        ],
        'timeout' => 60,
        'alias_name' => 'catalog_storefront',
        'source_prefix' => 'catalog_storefront_v',
        'source_current_version' => 1
    ],
```

Service implementation of ScopeConfigInterface is reading configuration from deployment configuration files, it is supporting
default and store scope and has configuration fallback between  these scopes.

Add following configuration to your deployment config files:

         'system' => [
                'default' => [
                    'search_store_front' => [
                        'search' => [
                            'engine' => 'storefrontElasticsearch6' // important node to resolve search engine
                        ]
                    ],
                    'catalog' => [
                        'frontend' => [
                            'flat_catalog_category' => 0
                        ],
                        'layered_navigation' => [
                            'price_range_calculation' => 'auto',
                            'interval_division_limit' => 9,
                            'price_range_step' => 10,
                            'price_range_max_intervals' => 10,
                            'one_price_interval' => 0,
                            'display_product_count' => 0
                        ]
                    ]
                ]
            ]

## Logging

In case of error occurs log file storefront-catalog.log will be created

To enable debug logging add the following configuration to env.php:

```
'dev' => [
    'debug' => [
        'debug_logging' => 1,
        'debug_extended' => 1, // extended info will be added. Be aware of log size.
    ]
]
```

In case of debug logging is enabled log file storefront-catalog-debug.log will be created