# WebapiSecurity

**WebapiSecurity** enables access management of some Web API resources.
If checkbox is enabled in backend through: Stores -> Configuration -> Services -> Magento Web API -> Web Api Security
then the security of all of the services outlined in app/code/Magento/WebapiSecurity/etc/di.xml would be loosened. You may modify this list to customize which services should follow this behavior.
By loosening the security, these services would allow access anonymously (by anyone).
