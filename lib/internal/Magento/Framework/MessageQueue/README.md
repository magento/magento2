This component is designed to provide Message Queue Framework.

## DTO serialization

Message payloads using `object_interface` topics are serialized through the Web API data processors.

- Getter methods define the payload fields; if a getter and a public property map to the same field, the getter wins.
- Public properties without getters are supported, including promoted/readonly properties, and are converted to
  snake_case field names.
- Constructor hydration accepts both camelCase and snake_case keys to support promoted/readonly DTOs on decode.
