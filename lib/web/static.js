/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define('jsbuild', [
    'module'
], function (module) {
    'use strict';

    var requireLoad = require.load,
        build = module.config(),
        info;

    function collectData() {
        var result,
            size,
            key;

        result = {
            totalBytes: 0,
            sizes: {}
        };

        for (key in build) {
            size = (new Blob([build[key]])).size;

            result.sizes[key] = size;
            result.totalBytes += size;
        }

        result.unusedBytes  = result.totalBytes;
        result.totalFiles   = Object.keys(build).length;
        result.unusedFiles  = result.totalFiles;

        return result;
    }

    function removeBaseUrl(context, url) {
        var baseUrl = context.config.baseUrl || '',
            index = url.indexOf(baseUrl);

        if (~index) {
            url = url.substring(baseUrl.length - index);
        }

        return url;
    }

    function bytesToSize(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'],
            i;

        if (bytes === 0) {
            return '0 Byte';
        }

        i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));

        return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
    }

    function updateInfo(name) {
        var size = info.sizes[name];

        info.unusedBytes -= size;
        info.unusedFiles -= 1;
    }

    function getPercents(current, total){
        return (100 * current / total).toFixed(2) + '%';
    }

    function notify() {
        var unusedBytes = info.unusedBytes,
            unusedFiles = info.unusedFiles;

        console.log(
            'Unused files: ' +
            unusedFiles + ' / ' +
            getPercents(unusedFiles, info.totalFiles)
        );
        console.log('Unused size: ' +
            bytesToSize(unusedBytes) + ' / ' +
            getPercents(unusedBytes, info.totalBytes)
        );
        console.log('**********************');
    }

    require.load = function (context, moduleName, url) {
        var relative = removeBaseUrl(context, url),
            data = build[relative];

        if (data) {
            (new Function(data))();

            updateInfo(relative);
            notify();

            context.completeLoad(moduleName);
        } else {
            requireLoad.apply(require, arguments);
        }
    };

    info = collectData();

    notify();
});
