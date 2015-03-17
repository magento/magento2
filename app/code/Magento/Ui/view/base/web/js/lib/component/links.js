define([
    'ko',
    'underscore',
    'mageUtils',
    'Magento_Ui/js/lib/registry/registry'
], function (ko, _, utils, registry) {
    'use strict';

    function extractData(str) {
        var data = str.split(':');

        return {
            component: data[0],
            prop: data[1]
        };
    }

    function update(target, prop, value) {
        if (_.isFunction(target[prop])) {
            target[prop](value);
        } else if (target.set) {
            target.set(prop, value);
        } else {
            target[prop] = value;
        }
    }

    function imports(owner, target, ownerProp, targetProp, auto) {
        var callback = update.bind(null, owner, ownerProp),
            value;

        value = target.get ?
            target.get(targetProp) :
            utils.nested(target, targetProp);

        if (ko.isObservable(value)) {
            value.subscribe(callback);
            value = value();
        } else if (target.on) {
            target.on(targetProp, callback);
        }

        if (auto) {
            callback(value);
        }
    }

    function exports(owner, target, ownerProp, targetProp, auto) {
        var to = update.bind(null, target, targetProp);

        ownerProp = owner[ownerProp];

        ownerProp.subscribe(to);

        if (auto) {
            to(ownerProp());
        }
    }

    function links(owner, target, ownerProp, direction) {
        if (!ko.isObservable(owner[ownerProp])) {
            owner.observe(ownerProp);
        }

        target = extractData(target);

        registry.get(target.component, function (component) {
            var args = [owner, component, ownerProp, target.prop, true];

            switch (direction) {
            case 'imports':
            case 'both':
                imports.apply(null, args);
                break;

            case 'exports':
            case 'both':
                exports.apply(null, args);
                break;
            }
        });
    }

    function listen(owner, target, callback) {
        target = extractData(target);

        if (!target.prop) {
            target.prop = target.component;
            target.component = owner.name;
        }

        registry.get(target.component, function (component) {
            imports(owner, component, callback, target.prop);
        });
    }

    return {
        setLinks: function (data, direction) {
            var owner = this;

            _.each(data, function (target, prop) {
                links(owner, target, prop, direction);
            });
        },

        setListners: function (listeners) {
            var owner = this;

            _.each(listeners, function (callbacks, sources) {
                sources = sources.split(' ');
                callbacks = callbacks.split(' ');

                sources.forEach(function (target) {
                    callbacks.forEach(function (callback) {
                        listen(owner, target, callback);
                    });
                });
            });
        }
    };
});
