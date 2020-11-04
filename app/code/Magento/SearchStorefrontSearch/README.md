Magento_SearchStorefrontSearch module introduces basic search functionality and provides interfaces that allow to implement search for specific module.

### Search engine configuration
Module expects definition of search engine `search_store_front/search/engine` and scope `default`.
 Put following configuration to `env.php` or `config.php`:

        'system' => [
            'default' => [
                'search_store_front' => [
                    'search' => [
                        'engine' => 'storefrontElasticsearch6'
                    ]
                ]
            ]
        ]


### TODO List:
+ search synonyms feature at the moment stubbed - to make service independent `Magento\SearchStorefrontSearch\ModelSynonymReader`
 should not read from DB (or should?)