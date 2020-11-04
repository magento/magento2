<p align="center">
    <a href="https://magento.com">
        <img src="https://static.magento.com/sites/all/themes/magento/logo.svg" width="300px" alt="Magento Commerce" />
    </a>
</p>

## Storefront Search Service
will provide some description soon

## GRPC up (local php)
1. Run bin/magento storefront:grpc:init \\\Magento\\\SearchStorefrontApi\\\Api\\\SearchProxyServer
2. ./vendor/grpc-server

## GRPC-UI (local, MacOs)
1. brew install grpcui
2. grpcui -plaintext -proto search.proto -port 8080 -bind 0.0.0.0 -import-path path_to_your_magento_project_root localhost:9001
