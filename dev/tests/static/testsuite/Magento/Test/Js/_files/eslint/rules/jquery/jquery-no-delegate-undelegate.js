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
        if (node.callee.type !== 'MemberExpression') return
        if (!['delegate', 'undelegate'].includes(node.callee.property.name)) return

        if (utils.isjQuery(node)) {
          context.report({
            node: node,
            message: 'jQuery $.delegate and $.undelegate are deprecated, use $.on and $.off instead'
          })
        }
      }
    }
  }
}
