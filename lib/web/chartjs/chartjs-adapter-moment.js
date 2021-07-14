/*!
  * chartjs-adapter-moment v1.0.0
  * https://www.chartjs.org
  * (c) 2021 chartjs-adapter-moment Contributors
  * Released under the MIT license
  */
(function (global, factory) {
typeof exports === 'object' && typeof module !== 'undefined' ? factory(require('moment'), require('chart.js')) :
typeof define === 'function' && define.amd ? define(['moment', 'chart.js'], factory) :
(global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.moment, global.Chart));
}(this, (function (moment, chart_js) { 'use strict';

function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

var moment__default = /*#__PURE__*/_interopDefaultLegacy(moment);

const FORMATS = {
  datetime: 'MMM D, YYYY, h:mm:ss a',
  millisecond: 'h:mm:ss.SSS a',
  second: 'h:mm:ss a',
  minute: 'h:mm a',
  hour: 'hA',
  day: 'MMM D',
  week: 'll',
  month: 'MMM YYYY',
  quarter: '[Q]Q - YYYY',
  year: 'YYYY'
};

chart_js._adapters._date.override(typeof moment__default['default'] === 'function' ? {
  _id: 'moment', // DEBUG ONLY

  formats: function() {
    return FORMATS;
  },

  parse: function(value, format) {
    if (typeof value === 'string' && typeof format === 'string') {
      value = moment__default['default'](value, format);
    } else if (!(value instanceof moment__default['default'])) {
      value = moment__default['default'](value);
    }
    return value.isValid() ? value.valueOf() : null;
  },

  format: function(time, format) {
    return moment__default['default'](time).format(format);
  },

  add: function(time, amount, unit) {
    return moment__default['default'](time).add(amount, unit).valueOf();
  },

  diff: function(max, min, unit) {
    return moment__default['default'](max).diff(moment__default['default'](min), unit);
  },

  startOf: function(time, unit, weekday) {
    time = moment__default['default'](time);
    if (unit === 'isoWeek') {
      weekday = Math.trunc(Math.min(Math.max(0, weekday), 6));
      return time.isoWeekday(weekday).startOf('day').valueOf();
    }
    return time.startOf(unit).valueOf();
  },

  endOf: function(time, unit) {
    return moment__default['default'](time).endOf(unit).valueOf();
  }
} : {});

})));
