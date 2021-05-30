'use strict'

const utils = require('./utils.js')

module.exports = {
  meta: {
    docs: {},
    schema: []
  },

  create: function(context) {
    return {
      CallExpression: function(node) {
        let names = ['load', 'unload', 'error'],
            name
        if (node.callee.type !== 'MemberExpression') return
        if (!names.includes(node.callee.property.name)) return
        if (utils.isjQuery(node)) {
          name = node.callee.property.name;
          context.report({
            node: node,
            message: 'jQuery.' + name + '() was removed, use .on("' + name + '", fn) instead.'
          })
        }
      }
    }
  }
}
