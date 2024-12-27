# Serialize

**Serialize** library provides interface *SerializerInterface* and multiple implementations:

 * *Json* - default implementation. Uses PHP native json_encode/json_decode functions;
 * *JsonHexTag* - default implementation. Uses PHP native json_encode/json_decode functions with `JSON_HEX_TAG` option enabled;
 * *Serialize* - less secure than *Json*, but gives higher performance on big arrays. Uses PHP native serialize/unserialize functions, does not unserialize objects on PHP 7.

Using *Serialize* implementation directly is discouraged, always use *SerializerInterface*, using *Serialize* implementation may lead to security vulnerabilities.
