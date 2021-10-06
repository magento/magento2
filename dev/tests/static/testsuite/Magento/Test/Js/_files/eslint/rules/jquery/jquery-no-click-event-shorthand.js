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
        let names = ['blur', 'focus', 'focusin', 'focusout', 'resize', 'scroll', 'dblclick', 'mousedown', 'mouseup', 'mousemove', 'mouseover', 'mouseout', 'mouseenter', 'mouseleave', 'change', 'select', 'submit', 'keydown', 'keypress', 'keyup', 'contextmenu', 'click'],
            name
        if (node.callee.type !== 'MemberExpression') return
        if (!names.includes(node.callee.property.name)) return
        if (utils.isjQuery(node)) {
          name = node.callee.property.name;
          context.report({
            node: node,
            message: 'Instead of .' + name + '(fn) use .on("' + name + '", fn). Instead of .' + name + '() use .trigger("' + name + '")'
          })
        }
      }
    }
  }
}
