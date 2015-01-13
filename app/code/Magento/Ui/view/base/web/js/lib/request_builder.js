/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @returns {Function} Request builder function.
 */
define([
    'Magento_Ui/js/lib/utils'
], function(utils) {
    'use strict';

    /**
     * @param {String} - name of params set
     * @param {Object} - params to convert
     * @returns {String} - concatenated name/params pairs by custom logic and separator
     * @private
     */
    function parseObject(name, value) {
        var key,
            result = [];

        for (key in value) {
            result.push(name + '[' + key + ']' + '=' + value[key])
        }

        return result.join('&');
    }

    /**
     * @param {String} - name of property
     * @param {String} - corresponding value
     * @returns {String} - concatenated params by separator "="
     * @private
     */
    function parseValue(name, value) {
        return name + '=' + value;
    }

    /**
     * Extracts sorting parameters from object and returns string representation of it.
     * @param {Object} param - Sorting parameters object, e.g. { field: 'field_to_sort', dir: 'asc' }.
     * @returns {String} - Chunk of url string that represents sorting params
     * @private
     */
    function extractSortParams(params) {
        var result,
            sorting = params.sorting;

        if (typeof sorting === 'undefined') {
            return '';
        }

        result = '/sort/' + sorting.field + '/dir/' + sorting.direction;

        delete params.sorting;

        return result;
    }

    /**
     * Extracts pager parameters from an object and returns it's string representation.
     * @param {Object} params which contains "paging" params object.
     * @returns {String} - Chunk of url string that represents pager params
     * @private
     */
    function extractPagerParams(params) {
        var result,
            paging = params.paging;

        if (typeof paging === 'undefined') {
            return '';
        }

        result = '/limit/' + paging.pageSize + '/page/' + paging.current;

        delete params.paging;

        return result;
    }

    /**
     * Formats filter data according to the type of it's value.
     * @param {Object} filter - filter object to format.
     * @returns {String} - Chunk of url string that represents filter's params
     * @private
     */
    function formatFilter(filter) {
        var name = filter.field,
            value = filter.value;

        return typeof value !== 'object' ?
            parseValue(name, value) :
            parseObject(name, value);
    }

    /**
     * Formats and assembles filter data.
     * @param {Object} params - object containing "filter" array.
     * @returns {String} - Chunk of url string that represents filters
     * @private
     */
    function extractFilterParams(params) {
        var filters,
            result;

        filters = params.filter;

        if (typeof filters === 'undefined' || !filters.length) {
            return '';
        }

        result = filters.map(formatFilter).join('&');

        result = '/filter/' + utils.btoa(encodeURI(result));

        delete params.filter;

        return result;
    }

    return function(root, params) {
        var url,
            lastChar;

        lastChar = root.charAt(root.length - 1);

        if (lastChar === '/') {
            root = root.substr(0, root.length - 1);
        }

        url =
            root +
            extractSortParams(params) +
            extractPagerParams(params) +
            extractFilterParams(params);

        return url;
    };

});