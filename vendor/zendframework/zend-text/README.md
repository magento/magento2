# zend-text

`Zend\Text` is a component to work on text strings. It contains the subcomponents:

- `Zend\Text\Figlet` that enables developers to create a so called FIGlet text.
  A FIGlet text is a string, which is represented as ASCII art. FIGlets use a
  special font format, called FLT (FigLet Font). By default, one standard font is
  shipped with `Zend\Text\Figlet`, but you can download additional fonts [here]( http://www.figlet.org)
- `Zend\Text\Table` to create text based tables on the fly with different
  decorators. This can be helpful, if you either want to send structured data in
  text emails, which are used to have mono-spaced fonts, or to display table
  information in a CLI application. `Zend\Text\Table` supports multi-line
  columns, colspan and align as well.


- File issues at https://github.com/zendframework/zend-text/issues
- Documentation is at http://framework.zend.com/manual/current/en/index.html#zend-text
