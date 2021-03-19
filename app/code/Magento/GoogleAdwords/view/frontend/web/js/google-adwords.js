/**
/* jscs:disable */
/* eslint-disable */
define([
    'jquery',
    'mage/cookies'
], function ($) {
    'use strict';
    /**
     * @param {Object} config
     */
    return function (config) {
        console.log(document.URL);
        function injectGlobalSiteTag(globalSiteTagSrc) {
            /* Global site tag (gtag.js) - Google Analytics */
            // console.log(document.URL);
            // var gtagScript = document.createElement('script');
            // gtagScript.type = 'text/javascript';
            // gtagScript.async = true;
            // gtagScript.src = globalSiteTagSrc;
            // $(document).head.appendChild(gtagScript);
        }
        function initGtagConfig() {
            // window.dataLayer = $(window).dataLayer || [];
            // function gtag(){dataLayer.push(arguments);}
            // gtag('js', new Date());
            // gtag('set', 'dYjhlMD', true);
            // gtag('config', 'AW-857842207');
        }        
        if (window.gtag) {
            // console.log(config.googleAdwordsConfig.conversionId);
            // gtag('config', 'AW-857842207');
        } else {
            // console.log(config.googleAdwordsConfig.globalSiteTagSrc); // test
            // injectGlobalSiteTag(config.googleAdwordsConfig.globalSiteTagSrc);
            // console.log(config.googleAdwordsConfig.developerId); // test
            // initGtagConfig();
        }
    }
});
