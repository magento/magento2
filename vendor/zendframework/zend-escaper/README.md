# zend-escaper

The OWASP Top 10 web security risks study lists Cross-Site Scripting (XSS) in
second place. PHP’s sole functionality against XSS is limited to two functions
of which one is commonly misapplied. Thus, the `Zend\Escaper` component was written.
It offers developers a way to escape output and defend from XSS and related
vulnerabilities by introducing contextual escaping based on peer-reviewed rules.


- File issues at https://github.com/zendframework/zend-escaper/issues
- Documentation is at http://framework.zend.com/manual/current/en/index.html#zend-escaper
