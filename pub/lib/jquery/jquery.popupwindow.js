/*jshint browser:true jquery:true window:true*/
(function($){
    $.fn.popupWindow = function(instanceSettings) {
        return this.each(function() {
            $(this).click(function() {
                $.fn.popupWindow.defaultSettings = {
                    centerBrowser: 0, // center window over browser window? {1 (YES) or 0 (NO)}. overrides top and left
                    centerScreen: 0, // center window over entire screen? {1 (YES) or 0 (NO)}. overrides top and left
                    height: 500, // sets the height in pixels of the window.
                    left: 0, // left position when the window appears.
                    location: 0, // determines whether the address bar is displayed {1 (YES) or 0 (NO)}.
                    menubar: 0, // determines whether the menu bar is displayed {1 (YES) or 0 (NO)}.
                    resizable: 0, // whether the window can be resized {1 (YES) or 0 (NO)}. Can also be overloaded using resizable.
                    scrollbars: 0, // determines whether scrollbars appear on the window {1 (YES) or 0 (NO)}.
                    status: 0, // whether a status line appears at the bottom of the window {1 (YES) or 0 (NO)}.
                    width: 500, // sets the width in pixels of the window.
                    windowName: null, // name of window set from the name attribute of the element that invokes the click
                    windowURL: null, // url used for the popup
                    top: 0, // top position when the window appears.
                    toolbar: 0 // determines whether a toolbar (includes the forward and back buttons) is displayed {1 (YES) or 0 (NO)}.
                };

                var _settings = $.extend({}, $.fn.popupWindow.defaultSettings, instanceSettings || {});

                var _windowFeatures =
                    'height=' + _settings.height +
                    ',width=' + _settings.width +
                    ',toolbar=' + _settings.toolbar +
                    ',scrollbars=' + _settings.scrollbars +
                    ',status=' + _settings.status +
                    ',resizable=' + _settings.resizable +
                    ',location=' + _settings.location +
                    ',menuBar=' + _settings.menubar;

                _settings.windowName = _settings.windowName || this.name;
                _settings.windowURL = _settings.windowURL || this.href;

                var _centeredY, _centeredX;

                if(_settings.centerBrowser){
                    if ($.browser.msie) { //hacked together for IE browsers
                        _centeredY = (window.screenTop - 120) + ((((document.documentElement.clientHeight + 120)/2) - (_settings.height/2)));
                        _centeredX = window.screenLeft + ((((document.body.offsetWidth + 20)/2) - (_settings.width/2)));
                    } else {
                        _centeredY = window.screenY + (((window.outerHeight/2) - (_settings.height/2)));
                        _centeredX = window.screenX + (((window.outerWidth/2) - (_settings.width/2)));
                    }
                    window.open(_settings.windowURL, _settings.windowName, _windowFeatures+',left=' + _centeredX +',top=' + _centeredY).focus();
                } else if(_settings.centerScreen) {
                    _centeredY = (screen.height - _settings.height)/2;
                    _centeredX = (screen.width - _settings.width)/2;
                    window.open(_settings.windowURL, _settings.windowName, _windowFeatures+',left=' + _centeredX +',top=' + _centeredY).focus();
                } else {
                    window.open(_settings.windowURL, _settings.windowName, _windowFeatures+',left=' + _settings.left +',top=' + _settings.top).focus();
                }

                return false;
            });
        });
    };
})(jQuery);
