# Translate

Magento provides an *Inline Translation* tool that allows inline editing of phrases that are passed through translation mechanism. The phrases are edited by the end-user stored in database dictionaries. **Translate** library provides framework to support inline translation. The following components are provided in this library for inline translation:

 * Parser - Parser parses and saves edited translation, and replaces html body with translation wrapping
   * *ParserInterface, ParserFactory*
 * Provider - Provider returns instance of inline translate class
   * *ProviderInterface* and a *Provider*
 * Configuration - It can configure inline translation to be active or inactive or to allow client ip or not.
   * *ConfigInterface*
 * State - It can disable, enable, suspend and resume inline translation.
   * *StateInterface* and a *State* class
 * Resource - It stores and retrieve translation array
   * *ResourceInterface*