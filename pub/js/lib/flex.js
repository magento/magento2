/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     js
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Flex maintance object
 *
 *
 */
Flex = {};
Flex.currentID = 0;
Flex.uniqId = function() {
    return 'flexMovieUID'+( ++Flex.currentID );
};

/**
 * Check flash player version for required version
 *
 * @param Number major
 * @param Number minor
 * @param Number revision
 * @return Boolean
 */
Flex.checkFlashPlayerVersion = function(major, minor, revision) {
    var version = Flex.getFlashPlayerVersion();

    if (version === false) {
        return false;
    }

    var requestedVersion = Flex.transformVersionToFloat([major, minor, revision], 5);
    var currentVersion = Flex.transformVersionToFloat(version, 5);

    return requestedVersion <= currentVersion;
};

/**
 * Get flash player version in internet explorer
 * by creating of test ActiveXObjects
 *
 * @return String|Boolean
 */
Flex._getFlashPlayerVersionAsActiveX = function () {
    var versions = [
        {'default': '7.0.0', 'code':'ShockwaveFlash.ShockwaveFlash.7', 'variable':true},
        {'default': '6.0.0', 'code':'ShockwaveFlash.ShockwaveFlash.6', 'variable':true, 'acceess':true},
        {'default': '3.0.0', 'code':'ShockwaveFlash.ShockwaveFlash.3', 'variable':false},
        {'default': '2.0.0', 'code':'ShockwaveFlash.ShockwaveFlash', 'variable':false},
    ];

    var detector = function (options) {
        var activeXObject = new ActiveXObject(options.code);
        if (options.access && options.variable) {
            activeXObject.AllowScriptAccess = 'always';
        }

        if (options.variable) {
            return activeXObject.GetVariable('$version');
        }

        return options['default'];
    }

    var version = false;

    for (var i = 0, l = versions.length; i < l; i++) {
        try {
            version = detector(versions[i]);
            return version;
        } catch (e) {}
    }

    return false;
};

/**
 * Transforms version string like 1.0.0 to array [1,0,0]
 *
 * @param String|Array version
 * @return Array|Boolean
 */
Flex.transformVersionToArray = function (version) {
    if (!Object.isString(version)) {
        return false;
    }

    var versions = version.match(/[\d]+/g);

    if (versions.length > 3) {
        return versions.slice(0,3);
    } else if (versions.length) {
        return versions;
    }



    return false;
};

/**
 * Transforms version string like 1.1.1 to float 1.00010001
 *
 * @param String|Array version
 * @param Number range - percition range between version digits
 * @return Array
 */
Flex.transformVersionToFloat = function (version, range) {
    if (Object.isString(version)) {
        version = Flex.transformVersionToArray(version)
    }

    if (Object.isArray(version)) {
        var result = 0;
        for (var i =0, l=version.length; i < l; i++) {
            result += parseFloat(version[i]) / Math.pow(10, range*i);
        }

        return result;
    }

    return false;
};

/**
 * Return flash player version as array of 0=major, 1=minor, 2=revision
 *
 * @return Array|Boolean
 */
Flex.getFlashPlayerVersion = function () {
    if (Flex.flashPlayerVersion) {
        return Flex.flashPlayerVersion;
    }

    var version = false;
    if (navigator.plugins != null && navigator.plugins.length > 0) {
       if (navigator.mimeTypes && navigator.mimeTypes.length > 0) {
          if (navigator.mimeTypes['application/x-shockwave-flash'] &&
              !navigator.mimeTypes['application/x-shockwave-flash'].enabledPlugin) {
             return false;
          }
       }
       var flashPlugin = navigator.plugins['Shockwave Flash'] || navigator.plugins['Shockwave Flash 2.0'];
       version = Flex.transformVersionToArray(flashPlugin.description);
    } else {
       version = Flex.transformVersionToArray(Flex._getFlashPlayerVersionAsActiveX());
    }

    Flex.flashPlayerVersion = version;
    return version;
};

Flex.Object = Class.create({
    /**
     * Initialize object from configuration, where configuration keys,
     * is set of tag attributes for object or embed
     *
     * @example
     * new Flex.Object({'src':'path/to/flashmovie.swf'});
     *
     * @param Object config
     * @return void
     */
    initialize: function (config) {
        this.isIE  = Prototype.Browser.IE;
        this.isWin = (navigator.appVersion.toLowerCase().indexOf("win") != -1) ? true : false;
        this.attributes = {
             quality:"high",
             pluginspage: "http://www.adobe.com/go/getflashplayer",
             type: "application/x-shockwave-flash",
             allowScriptAccess: "always",
             classid: "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
        };
        this.bridgeName = '';
        this.bridge = false;
        this.setAttributes( config );
        this.applied = false;

        var myTemplatesPattern = /(^|.|\r|\n)(\{(.*?)\})/;
        if(this.detectFlashVersion(9, 0, 28)) {
            if(this.isIE) {
                this.template = new Template( '<object {objectAttributes}><param name="allowFullScreen" value="true"/>{objectParameters}</object>', myTemplatesPattern )
            } else {
                this.template = new Template( '<embed {embedAttributes} allowfullscreen="true" />', myTemplatesPattern );
            }
        } else {
            this.template = new Template(  'This content requires the Adobe Flash Player. '
                                               +' <a href=http://www.adobe.com/go/getflash/>Get Flash</a>', myTemplatesPattern );
        }

        this.parametersTemplate = new Template( '<param name="{name}" value="{value}" />', myTemplatesPattern );
        this.attributesTemplate = new Template( ' {name}="{value}" ', myTemplatesPattern );
    },
    /**
     * Set object attribute for generation of html tags
     *
     * @param Sting name
     * @param Object value
     * @return void
     */
    setAttribute : function( name, value ) {
        if(!this.applied) {
            this.attributes[name] = value;
        }
    },
    /**
     * Retrive object attribute value used for generation in html tags
     *
     * @param Sting name
     * @return Object
     */
    getAttribute : function( name ) {
        return this.attributes[name];
    },
    /**
     * Set object attributes in one call
     *
     * @param Object attributesList
     * @return void
     */
    setAttributes : function( attributesList ) {
        $H(attributesList).each(function(pair){
            this.setAttribute(pair.key, pair.value);
        }.bind(this));
    },
    /**
     * Retrieve all object attributes
     *
     * @return Object
     */
    getAttributes : function( ) {
        return this.attributes;
    },
    /**
     * Applies generated HTML content to specified HTML tag
     *
     * @param String|DOMELement container
     * @return void
     */
    apply : function(container) {
        if (!this.applied)    {
            this.setAttribute("id", Flex.uniqId());
            this.preInitBridge();
            var readyHTML = this.template.evaluate(this.generateTemplateValues());
            $(container).update(readyHTML);
        }
        this.applied = true;
    },
    /**
     * Applies generated HTML content to window.document
     *
     * @return void
     */
    applyWrite : function( ) {
        if (!this.applied)    {
            this.setAttribute( "id", Flex.uniqId());
            this.preInitBridge();
            var readyHTML = this.template.evaluate( this.generateTemplateValues() );
            document.write( readyHTML );
        }
        this.applied = true;
    },
    /**
     * Preinitialize FABridge values
     *
     * @return void
     */
    preInitBridge: function () {
        this.bridgeName = this.getAttribute('id') + 'bridge';
        var flashVars = this.getAttribute('flashVars') || this.getAttribute('flashvars') || '';
        if (flashVars != '') {
            flashVars += '&';
        }
        flashVars += 'bridgeName=' + this.bridgeName;
        this.setAttribute('flashVars', flashVars);
        var scopeObj = this;
        FABridge.addInitializationCallback(
             this.bridgeName,
             function () {
                 scopeObj.bridge = this.root();
                 scopeObj.initBridge();
             }
        );
    },
    /**
     * Initialize bridge callback passed to FABridge,
     * calls internal callback if it's presented
     *
     * @return void
     */
    initBridge: function() {
        if(this.onBridgeInit) {
            this.onBridgeInit(this.getBridge());
        }
    },
    /**
     * Retrieve FABridge instance for this object
     *
     * @return Object
     */
    getBridge : function() {
        return this.bridge;
    },
    /**
     * Generate temaplate values object for creation of flash player plugin movie HTML
     *
     * @return Object
     */
    generateTemplateValues : function() {
        var attributesMap = {
            embed: {
                'movie':'src',
                'id':'name',
                'flashvars': 'flashVars',
                'classid':false,
                'codebase':false
            },
            object: {
                'pluginspage':false,
                'src':'movie',
                'flashvars': 'flashVars',
                'type':false,
                'inline': [
                    'type', 'classid', 'codebase', 'id', 'width', 'height',
                    'align', 'vspace', 'hspace', 'class', 'title', 'accesskey', 'name',
                    'tabindex'
                ]
            }
        };
        var embedAttributes = {};
        var objectAttributes = {};
        var parameters = {};
        $H(this.attributes).each(function(pair) {
            var attributeName = pair.key.toLowerCase();
            this.attributes[pair.key] = this.escapeAttributes(pair.value);

            // Retrieve mapped attribute names
            var attributeNameInObject = (attributesMap.object[attributeName] ? attributesMap.object[attributeName] : attributeName);
            var attributeNameInEmbed = (attributesMap.embed[attributeName] ? attributesMap.embed[attributeName] : attributeName);

            if (attributesMap.object[attributeName] !== false) {
                if (attributesMap.object.inline.indexOf(attributeNameInObject) !== -1) { // If it included in default object attribute
                    objectAttributes[attributeNameInObject] = this.attributes[pair.key];
                } else { // otherwise add it to parameters tag list
                    parameters[attributeNameInObject] = this.attributes[pair.key];
                }
            }

            if (attributesMap.embed[attributeName] !== false) { // If this attribute not ignored for flash in Gecko Browsers
                embedAttributes[attributeNameInEmbed] = this.attributes[pair.key];
            }
        }.bind(this));

        var result = {
            objectAttributes: '',
            objectParameters: '',
            embedAttributes : ''
        };


        $H(objectAttributes).each(function(pair){
             result.objectAttributes += this.attributesTemplate.evaluate({
                 name:pair.key,
                 value:pair.value
             });
        }.bind(this));

        $H(embedAttributes).each(function(pair){
             result.embedAttributes += this.attributesTemplate.evaluate({
                 name:pair.key,
                 value:pair.value
             });
        }.bind(this));

        $H(parameters).each(function(pair){
             result.objectParameters += this.parametersTemplate.evaluate({
                 name:pair.key,
                 value:pair.value
             });
        }.bind(this));

        return result;
    },
    /**
     * Escapes attributes for generation of valid HTML
     *
     * @return String
     */
    escapeAttributes: function (value) {
        if(typeof value == 'string') {
            return value.escapeHTML();
        } else {
            return value;
        }
    },
    /**
     * Detects needed flash player version
     *
     * @param Number major
     * @param Number minor
     * @param Number revision
     * @return Boolean
     */
    detectFlashVersion: function (major, minor, revision) {
        return Flex.checkFlashPlayerVersion(major, minor, revision);
    }
});
