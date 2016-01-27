/*!
  Knockout Fast Foreach v0.4.1 (2015-07-17T14:06:15.974Z)
  By: Brian M Hunt (C) 2015
  License: MIT

  Adds `fastForEach` to `ko.bindingHandlers`.
*/
(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    define(['knockout'], factory);
  } else if (typeof exports === 'object') {
    module.exports = factory(require('knockout'));
  } else {
    root.KnockoutFastForeach = factory(root.ko);
  }
}(this, function (ko) {
  "use strict";
// index.js
// --------
// Fast For Each
//
// Employing sound techniques to make a faster Knockout foreach binding.
// --------

//      Utilities

// from https://github.com/jonschlinkert/is-plain-object
function isPlainObject(o) {
  return !!o && typeof o === 'object' && o.constructor === Object;
}

// From knockout/src/virtualElements.js
var commentNodesHaveTextProperty = document && document.createComment("test").text === "<!--test-->";
var startCommentRegex = commentNodesHaveTextProperty ? /^<!--\s*ko(?:\s+([\s\S]+))?\s*-->$/ : /^\s*ko(?:\s+([\s\S]+))?\s*$/;
var supportsDocumentFragment = document && typeof document.createDocumentFragment === "function";
function isVirtualNode(node) {
  return (node.nodeType === 8) && startCommentRegex.test(commentNodesHaveTextProperty ? node.text : node.nodeValue);
}


// Get a copy of the (possibly virtual) child nodes of the given element,
// put them into a container, then empty the given node.
function makeTemplateNode(sourceNode) {
  var container = document.createElement("div");
  var parentNode;
  if (sourceNode.content) {
    // For e.g. <template> tags
    parentNode = sourceNode.content;
  } else if (sourceNode.tagName === 'SCRIPT') {
    parentNode = document.createElement("div");
    parentNode.innerHTML = sourceNode.text;
  } else {
    // Anything else e.g. <div>
    parentNode = sourceNode;
  }
  ko.utils.arrayForEach(ko.virtualElements.childNodes(parentNode), function (child) {
    // FIXME - This cloneNode could be expensive; we may prefer to iterate over the
    // parentNode children in reverse (so as not to foul the indexes as childNodes are
    // removed from parentNode when inserted into the container)
    if (child) {
      container.insertBefore(child.cloneNode(true), null);
    }
  });
  return container;
}

function insertAllAfter(containerNode, nodeOrNodeArrayToInsert, insertAfterNode) {
  var frag, len, i;
  // poor man's node and array check, should be enough for this
  if (typeof nodeOrNodeArrayToInsert.nodeType !== "undefined" && typeof nodeOrNodeArrayToInsert.length === "undefined") {
    throw new Error("Expected a single node or a node array");
  }

  if (typeof nodeOrNodeArrayToInsert.nodeType !== "undefined") {
    ko.virtualElements.insertAfter(containerNode, nodeOrNodeArrayToInsert, insertAfterNode);
    return;
  }

  if (nodeOrNodeArrayToInsert.length === 1) {
    ko.virtualElements.insertAfter(containerNode, nodeOrNodeArrayToInsert[0], insertAfterNode);
    return;
  }

  if (supportsDocumentFragment) {
    frag = document.createDocumentFragment();

    for (i = 0, len = nodeOrNodeArrayToInsert.length; i !== len; ++i) {
      frag.appendChild(nodeOrNodeArrayToInsert[i]);
    }
    ko.virtualElements.insertAfter(containerNode, frag, insertAfterNode);
  } else {
    // Nodes are inserted in reverse order - pushed down immediately after
    // the last node for the previous item or as the first node of element.
    for (i = nodeOrNodeArrayToInsert.length - 1; i >= 0; --i) {
      var child = nodeOrNodeArrayToInsert[i];
      if (!child) {
        return;
      }
      ko.virtualElements.insertAfter(containerNode, child, insertAfterNode);
    }
  }
}

// Mimic a KO change item 'add'
function valueToChangeAddItem(value, index) {
  return {
    status: 'added',
    value: value,
    index: index
  };
}

function isAdditionAdjacentToLast(changeIndex, arrayChanges) {
  return changeIndex > 0 &&
    changeIndex < arrayChanges.length &&
    arrayChanges[changeIndex].status === "added" &&
    arrayChanges[changeIndex - 1].status === "added" &&
    arrayChanges[changeIndex - 1].index === arrayChanges[changeIndex].index - 1;
}

function FastForEach(spec) {
  this.element = spec.element;
  this.container = isVirtualNode(this.element) ?
                   this.element.parentNode : this.element;
  this.$context = spec.$context;
  this.data = spec.data;
  this.as = spec.as;
  this.noContext = spec.noContext;
  this.templateNode = makeTemplateNode(
    spec.name ? document.getElementById(spec.name).cloneNode(true) : spec.element
  );
  this.afterQueueFlush = spec.afterQueueFlush;
  this.beforeQueueFlush = spec.beforeQueueFlush;
  this.changeQueue = [];
  this.lastNodesList = [];
  this.indexesToDelete = [];
  this.rendering_queued = false;

  // Remove existing content.
  ko.virtualElements.emptyNode(this.element);

  // Prime content
  var primeData = ko.unwrap(this.data);
  if (primeData.map) {
    this.onArrayChange(primeData.map(valueToChangeAddItem));
  }

  // Watch for changes
  if (ko.isObservable(this.data)) {
    if (!this.data.indexOf) {
      // Make sure the observable is trackable.
      this.data = this.data.extend({trackArrayChanges: true});
    }
    this.changeSubs = this.data.subscribe(this.onArrayChange, this, 'arrayChange');
  }
}


FastForEach.animateFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame ||
  window.mozRequestAnimationFrame || window.msRequestAnimationFrame ||
  function(cb) { return window.setTimeout(cb, 1000 / 60); };


FastForEach.prototype.dispose = function () {
  if (this.changeSubs) {
    this.changeSubs.dispose();
  }
};


// If the array changes we register the change.
FastForEach.prototype.onArrayChange = function (changeSet) {
  var self = this;
  var changeMap = {
    added: [],
    deleted: []
  };
  for (var i = 0, len = changeSet.length; i < len; i++) {
    // the change is appended to a last change info object when both are 'added' and have indexes next to each other
    // here I presume that ko is sending changes in monotonic order (in index variable) which happens to be true, tested with push and splice with multiple pushed values
    if (isAdditionAdjacentToLast(i, changeSet)) {
      var batchValues = changeMap.added[changeMap.added.length - 1].values;
      if (!batchValues) {
        // transform the last addition into a batch addition object
        var lastAddition = changeMap.added.pop();
        var batchAddition = {
          isBatch: true,
          status: 'added',
          index: lastAddition.index,
          values: [lastAddition.value]
        };
        batchValues = batchAddition.values;
        changeMap.added.push(batchAddition);
      }
      batchValues.push(changeSet[i].value);
    } else {
      changeMap[changeSet[i].status].push(changeSet[i]);
    }
  }
  if (changeMap.deleted.length > 0) {
    this.changeQueue.push.apply(this.changeQueue, changeMap.deleted);
    this.changeQueue.push({status: 'clearDeletedIndexes'});
  }
  this.changeQueue.push.apply(this.changeQueue, changeMap.added);
  // Once a change is registered, the ticking count-down starts for the processQueue.
  if (this.changeQueue.length > 0 && !this.rendering_queued) {
    this.rendering_queued = true;
    FastForEach.animateFrame.call(window, function () { self.processQueue(); });
  }
};


// Reflect all the changes in the queue in the DOM, then wipe the queue.
FastForEach.prototype.processQueue = function () {
  var self = this;

  // Callback so folks can do things before the queue flush.
  if (typeof this.beforeQueueFlush === 'function') {
    this.beforeQueueFlush(this.changeQueue);
  }

  ko.utils.arrayForEach(this.changeQueue, function (changeItem) {
    // console.log(self.data(), "CI", JSON.stringify(changeItem, null, 2), JSON.stringify($(self.element).text()))
    self[changeItem.status](changeItem);
    // console.log("  ==> ", JSON.stringify($(self.element).text()))
  });
  this.rendering_queued = false;
  // Callback so folks can do things.
  if (typeof this.afterQueueFlush === 'function') {
    this.afterQueueFlush(this.changeQueue);
  }
  this.changeQueue = [];
};


// Process a changeItem with {status: 'added', ...}
FastForEach.prototype.added = function (changeItem) {
  var index = changeItem.index;
  var valuesToAdd = changeItem.isBatch ? changeItem.values : [changeItem.value];
  var referenceElement = this.lastNodesList[index - 1] || null;
  // gather all childnodes for a possible batch insertion
  var allChildNodes = [];

  for (var i = 0, len = valuesToAdd.length; i < len; ++i) {
    var templateClone = this.templateNode.cloneNode(true);
    var childContext;

    if (this.noContext) {
      childContext = this.$context.extend({
        '$item': valuesToAdd[i]
      });
    } else {
      childContext = this.$context.createChildContext(valuesToAdd[i], this.as || null);
    }

    // apply bindings first, and then process child nodes, because bindings can add childnodes
    ko.applyBindingsToDescendants(childContext, templateClone);

    var childNodes = ko.virtualElements.childNodes(templateClone);
    // Note discussion at https://github.com/angular/angular.js/issues/7851
    allChildNodes.push.apply(allChildNodes, Array.prototype.slice.call(childNodes));
    this.lastNodesList.splice(index + i, 0, childNodes[childNodes.length - 1]);
  }

  insertAllAfter(this.element, allChildNodes, referenceElement);
};


// Process a changeItem with {status: 'deleted', ...}
FastForEach.prototype.deleted = function (changeItem) {
  var index = changeItem.index;
  var ptr = this.lastNodesList[index],
      // We use this.element because that will be the last previous node
      // for virtual element lists.
      lastNode = this.lastNodesList[index - 1] || this.element;
  do {
    ptr = ptr.previousSibling;
    ko.removeNode((ptr && ptr.nextSibling) || ko.virtualElements.firstChild(this.element));
  } while (ptr && ptr !== lastNode);
  // The "last node" in the DOM from which we begin our delets of the next adjacent node is
  // now the sibling that preceded the first node of this item.
  this.lastNodesList[index] = this.lastNodesList[index - 1];
  this.indexesToDelete.push(index);
};


// We batch our deletion of item indexes in our parallel array.
// See brianmhunt/knockout-fast-foreach#6/#8
FastForEach.prototype.clearDeletedIndexes = function () {
  // We iterate in reverse on the presumption (following the unit tests) that KO's diff engine
  // processes diffs (esp. deletes) monotonically ascending i.e. from index 0 -> N.
  for (var i = this.indexesToDelete.length - 1; i >= 0; --i) {
    this.lastNodesList.splice(this.indexesToDelete[i], 1);
  }
  this.indexesToDelete = [];
};


ko.bindingHandlers.fastForEach = {
  // Valid valueAccessors:
  //    []
  //    ko.observable([])
  //    ko.observableArray([])
  //    ko.computed
  //    {data: array, name: string, as: string}
  init: function init(element, valueAccessor, bindings, vm, context) {
    var value = valueAccessor(),
        ffe;
    if (isPlainObject(value)) {
      value.element = value.element || element;
      value.$context = context;
      ffe = new FastForEach(value);
    } else {
      ffe = new FastForEach({
        element: element,
        data: ko.unwrap(context.$rawData) === value ? context.$rawData : value,
        $context: context
      });
    }
    ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
      ffe.dispose();
    });
    return {controlsDescendantBindings: true};
  },

  // Export for testing, debugging, and overloading.
  FastForEach: FastForEach
};

ko.virtualElements.allowedBindings.fastForEach = true;
}));