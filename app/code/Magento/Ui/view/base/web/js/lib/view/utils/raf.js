/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'es6-collections'
], function () {
    'use strict';

    var processMap = new WeakMap(),
        origRaf,
        raf;

    origRaf = window.requestAnimationFrame ||
        window.webkitRequestAnimationFrame ||
        window.mozRequestAnimationFrame ||
        window.onRequestAnimationFrame ||
        window.msRequestAnimationFrame ||
        function (callback) {
            window.setTimeout(callback, 1000 / 60);
        };

    /**
     * Creates new process object or extracts the
     * the existing one.
     *
     * @param {*} id - Process identifier.
     * @param {Number} fps - Required FPS count.
     * @returns {Object}
     */
    function getProcess(id, fps) {
        var process = processMap.get(id);

        if (!process) {
            process = {};
            processMap.set(id, process);
        }

        if (process.fps !== fps) {
            process.fps        = fps;
            process.interval   = 1000 / fps;
            process.update     = Date.now();
        }

        return process;
    }

    /**
     * Proxy method which delegates call to the 'requestAnimationFrame'
     * function and optionally can keep track of the FPS with which
     * provided function is called.
     *
     * @param {Function} callback - Callback function to be passed to 'requestAnimationFrame'.
     * @param {Number} [fps] - If specified, will update FPS counter for the provided function.
     * @returns {Number|Boolean} ID of request or a flag which indicates
     *      whether callback fits specified FPS.
     */
    raf = function (callback, fps) {
        var rafId = origRaf(callback);

        return fps ? raf.tick(callback, fps) : rafId;
    };

    /**
     * Updates FPS counter for the specified process
     * and returns a flag which indicates whether
     * counter value is equal or greater than the required FPS.
     *
     * @param {*} id - Process identifier.
     * @param {Number} fps - Required FPS count.
     * @returns {Boolean}
     */
    raf.tick = function (id, fps) {
        var process  = getProcess(id, fps),
            now      = Date.now(),
            delta    = now - process.update,
            interval = process.interval;

        if (fps >= 60 || delta >= interval) {
            process.update = now - delta % interval;

            return true;
        }

        return false;
    };

    return raf;
});
