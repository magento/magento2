define([
    './record'
], function (Record) {
    'use strict';

    return Record.extend({
        defaults: {
            template: 'ui/grid/editing/bulk',
            templates: {
                editors: {
                    select: {
                        caption: ' '
                    }
                }
            },
            modules: {
                editor: '${ $.editorProvider }'
            }
        },

        apply: function () {},

        isActionsColumn: function (column) { 
            return column.dataType === 'actions';
        }
    });
});
