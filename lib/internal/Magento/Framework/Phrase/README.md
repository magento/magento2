# Phrase

Class *\Magento\Framework\Phrase* calls renderer to make the translation of the text. **Phrase** provides *RendererInterface* and a few renderers to support different kinds of needs of translation of the text. Here are list of renderers in this library:

 * Placeholder render - it replaces placeholders with parameters for substitution. It is the default render if none is set for the Phrase.
 * Translate render - it is a base renderer that implements text translations.
 * Inline render - it adds inline translate part to text translation and returns the strings by a template.
 * Composite render - it can have several renderers, calls each renderer for processing the text. Array of renderer class names pass into composite render constructor as a parameter.