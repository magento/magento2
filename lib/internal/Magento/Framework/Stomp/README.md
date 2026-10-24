# Magento Message Queue: Dual RabbitMQ & ActiveMQ Support

## Overview

This implementation provides seamless support for both RabbitMQ (AMQP) and Apache ActiveMQ Artemis (STOMP) message queues. The system automatically routes messages based on connection configuration.

## Configuration

### Environment Configuration (env.php)

```php
<?php
return [
    // ... other config
    'queue' => [
        // Default AMQP (RabbitMQ) connection
        'amqp' => [
            'host' => 'localhost',
            'port' => 5672,
            'user' => 'guest',
            'password' => 'guest',
            'virtualhost' => '/'
        ],
        
        // STOMP (ActiveMQ Artemis) connections
        'stomp' => [
            'host' => 'localhost',
            'port' => 61613,
            'user' => 'artemis',
            'password' => 'artemis',
            'ssl' => false,
            'ssl_options' => [],
            // Performance tuning options
            'heartbeat_send' => 10000,     // 10 seconds
            'heartbeat_receive' => 10000,  // 10 seconds
            'read_timeout' => 250000       // 250ms
        ],
        
        // Multiple STOMP connections for different purposes
        'connections' => [
            'inventory_stomp' => [
                'host' => 'inventory-queue.example.com',
                'port' => 61613,
                'user' => 'inventory_user',
                'password' => 'inventory_pass',
                'ssl' => true,
                'ssl_options' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ],
            'catalog_stomp' => [
                'host' => 'catalog-queue.example.com', 
                'port' => 61613,
                'user' => 'catalog_user',
                'password' => 'catalog_pass'
            ]
        ]
    ]
];
```

### Queue Configuration (queue_topology.xml)

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/topology.xsd">
    
    <!-- RabbitMQ exchanges -->
    <exchange name="magento.catalog" type="topic" connection="amqp">
        <binding id="catalogBinding" topic="catalog.*" destinationType="queue" destination="catalog.updates"/>
    </exchange>
    
    <!-- ActiveMQ exchanges -->
    <exchange name="magento.inventory" type="topic" connection="stomp">
        <binding id="inventoryBinding" topic="inventory.*" destinationType="queue" destination="inventory.updates"/>
    </exchange>
    
    <!-- Multiple connection support -->
    <exchange name="magento.catalog.special" type="topic" connection="catalog_stomp">
        <binding id="catalogSpecialBinding" topic="catalog.special.*" destinationType="queue" destination="catalog.special.updates"/>
    </exchange>
</config>
```

### Consumer Configuration (queue_consumer.xml)

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/consumer.xsd">
    
    <!-- RabbitMQ consumers -->
    <consumer name="catalog.updates" queue="catalog.updates" connection="amqp" 
              handler="Magento\Catalog\Model\MessageQueue\UpdateHandler::execute"/>
    
    <!-- ActiveMQ consumers -->
    <consumer name="inventory.updates" queue="inventory.updates" connection="stomp"
              handler="Magento\Inventory\Model\MessageQueue\UpdateHandler::execute"/>
              
    <!-- Multiple connection consumers -->
    <consumer name="catalog.special.updates" queue="catalog.special.updates" connection="catalog_stomp"
              handler="Magento\Catalog\Model\MessageQueue\SpecialUpdateHandler::execute"/>
</config>
```

## Connection Type Resolution

The system automatically determines the connection type:

1. **AMQP**: Used when connection name is `amqp` or not found in STOMP configuration
2. **STOMP**: Used when connection name is `stomp` or found in `queue.connections` array

## Error Handling & Retry Logic

### Automatic Retry Patterns

The enhanced StompClient provides robust error handling:

```php
// Retryable error patterns
- 'connection' issues
- 'write frame' errors  
- 'broken pipe'
- 'network' timeouts
- 'AMQ229014' (ActiveMQ specific)
- 'AMQ229031' (Connection lost)
- 'disconnected' states
- 'socket' errors
```

### Retry Configuration

- **Max Retries**: 3 attempts
- **Progressive Backoff**: 100ms, 200ms, 400ms
- **Connection Reset**: On retryable errors
- **Health Checks**: Before each operation

## Performance Optimization

### ActiveMQ Tuning

```php
// env.php optimizations
'stomp' => [
    'host' => 'localhost',
    'port' => 61613,
    'user' => 'artemis', 
    'password' => 'artemis',
    // Reduce heartbeat for high-frequency operations
    'heartbeat_send' => 5000,      // 5 seconds
    'heartbeat_receive' => 5000,   // 5 seconds  
    // Adjust read timeout for responsiveness
    'read_timeout' => 100000       // 100ms
]
```

### Queue Clearing Optimization

The system provides two clearing mechanisms:

1. **REST API** (Preferred): Fast bulk clearing via Jolokia
2. **STOMP Protocol** (Fallback): Message-by-message clearing

## Troubleshooting

### Common Issues

#### 1. "Failed to send message after 3 attempts: Was not possible to write frame"

**Causes:**
- ActiveMQ connection lost
- Network connectivity issues
- Incorrect authentication
- Queue permissions

**Solutions:**
```bash
# Check ActiveMQ status
systemctl status activemq-artemis

# Verify network connectivity  
telnet localhost 61613

# Check queue permissions in ActiveMQ console
http://localhost:8161/console
```

#### 2. Connection Type Not Resolved

**Error:** `Unknown connection name 'stomp'`

**Solution:** Ensure proper configuration in env.php:
```php
'queue' => [
    'stomp' => [
        'host' => 'localhost',
        // ... other config
    ]
]
```

#### 3. SSL Connection Issues

**Solution:** Configure SSL options:
```php
'stomp' => [
    'ssl' => true,
    'ssl_options' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'cafile' => '/path/to/ca.pem'
    ]
]
```

### Debugging

Enable debug logging:

```php
// Add to env.php
'log' => [
    'channel' => [
        'message_queue' => [
            'handlers' => [
                'debug' => [
                    'type' => 'stream',
                    'path' => '/var/log/magento/message_queue.log',
                    'level' => 'debug'
                ]
            ]
        ]
    ]
]
```

## Migration Strategy

### Phase 1: Parallel Operation
1. Configure both AMQP and STOMP connections
2. Route new queues to STOMP
3. Keep existing queues on AMQP

### Phase 2: Gradual Migration  
1. Move non-critical queues to STOMP
2. Monitor performance and stability
3. Update consumer configurations

### Phase 3: Complete Migration
1. Move remaining queues to STOMP
2. Remove AMQP configuration
3. Update all queue configurations

## Monitoring

### Health Checks

```bash
# RabbitMQ
rabbitmqctl status

# ActiveMQ Artemis  
curl -u admin:admin http://localhost:8161/console/jolokia/read/org.apache.activemq.artemis:broker="0.0.0.0"/Started
```

### Queue Metrics

```bash
# View queue depth
curl -u admin:admin "http://localhost:8161/console/jolokia/read/org.apache.activemq.artemis:broker=\"0.0.0.0\",component=addresses,address=\"your.queue\",subcomponent=queues,routing-type=\"anycast\",queue=\"your.queue\"/MessageCount"
```

## Best Practices

1. **Use Connection Pools**: Configure multiple connections for high-load scenarios
2. **Monitor Queue Depth**: Set up alerts for queue buildup
3. **SSL in Production**: Always use SSL for production deployments
4. **Regular Health Checks**: Implement monitoring for both queue systems
5. **Graceful Degradation**: Plan fallback strategies for queue failures
