Files in this directory are Varnish Test Cases (VTC) and check the behavior of the VCL file shipped by `magento2`.

Varnish needs to be installed, but then the test scenarios can be run individually or all at once:

``` shell
varnishtest *.vtc
```

Documentation:
- varnishtest itself: https://varnish-cache.org/docs/trunk/reference/varnishtest.html
- VTC syntax: https://varnish-cache.org/docs/trunk/reference/vtc.html
