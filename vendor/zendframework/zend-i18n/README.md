# zend-i18n

`Zend\I18n` comes with a complete translation suite which supports all major
formats and includes popular features like plural translations and text domains.
The Translator component is mostly dependency free, except for the fallback to a
default locale, where it relies on the Intl PHP extension.

The translator itself is initialized without any parameters, as any configuration
to it is optional. A translator without any translations will actually do nothing
but just return the given message IDs.

- File issues at https://github.com/zendframework/zend-i18n/issues
- Documentation is at http://framework.zend.com/manual/current/en/index.html#zend-i18n
