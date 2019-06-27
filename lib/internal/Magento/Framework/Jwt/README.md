# JWT

Provides implementation for [JSON Web Tokens](https://tools.ietf.org/html/rfc7519). The current implementation wraps [JWT Framework](https://web-token.spomky-labs.com/) functionality to provide convenient interfaces and has the implementation only for JWS:

- `\Magento\Framework\Jwt\ManagementInterface` the main interface for developers which can be used for token encoding, decoding and validation.
- `\Magento\Framework\Jwt\KeyGeneratorInterface` - provides a possibility to implement own key generation. The default implementation is `\Magento\Framework\Jwt\KeyGenerator\CryptKey` and uses `crypt/key` from `env.php` to generate a secret key.
- `\Magento\Framework\Jwt\ClaimCheckerInterface` - provides a possibility to implement custom claim checkers which should throw `\Magento\Framework\Jwt\InvalidClaimException` in case of failed claim's validation.
- `\Magento\Framework\Jwt\SerializerInterface` - a customization point for specific serialization/deserialization implementation.
- `\Magento\Framework\Jwt\KeyGenerator\SecretKeyFactory` - should be used to create an instance of JWK in custom private key generator.

A custom private key generator and claims checkers can be configured via `di.xml` like in the following example:
```xml
<virtualType name="Magento\CustomModule\Model\VirtualClaimCheckerManager" type="Magento\Framework\Jwt\ClaimCheckerManager">
    <arguments>
        <argument name="checkers" xsi:type="array">
            <item name="errorNumber" xsi:type="string">Magento\CustomModule\Model\Response\ClaimChecker\ErrorNumber</item>
            <item name="exp" xsi:type="string">Magento\Framework\Jwt\ClaimChecker\ExpirationTime</item>
        </argument>
        <argument name="mandatoryClaims" xsi:type="array">
            <item name="error_number" xsi:type="string">ErrorNumber</item>
            <item name="exp" xsi:type="string">exp</item>
        </argument>
    </arguments>
</virtualType>

<virtualType name="Magento\CustomModule\Model\VirtualJwsManagement" type="Magento\Framework\Jwt\Jws\Management">
    <arguments>
        <argument name="keyGenerator" xsi:type="object">Magento\CustomModule\Model\ApiKeyGenerator</argument>
        <argument name="claimCheckerManager" xsi:type="object">Magento\CustomModule\Model\VirtualClaimCheckerManager</argument>
    </arguments>
</virtualType>
```
