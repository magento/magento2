define([
    'jquery',
    'uiComponent'
], function ($, Component) {
        'use strict';

    let getQuery = () => {
        let record = {
            city: '',
            temperature: 0
        };
        $.ajax({
            async: false,
            global: false,
            url: "/tsg_weather_widget/record/get",
            method: 'GET',
            success: function (res) {
                record.temperature = res.temperature;
                record.city = res.city;
            }
        });
        return record;
    };

        return Component.extend({
            defaults: {
                template: 'Tsg_WeatherWidget/weather-widget'
            },
            initialize: function () {
                this._super();
                let res = getQuery();

                this.city = res.city;
                this.temperature = res.temperature;

                this.observe(['city']);
                this.observe(['temperature']);

                setInterval(this.flush.bind(this), 60000);
            },

            flush: function() {
                let res = getQuery();
                this.city(res.city);
                this.temperature(res.temperature);
            }
        });
    }
);
