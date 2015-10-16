// REPEAT binding for Knockout http://knockoutjs.com/
// (c) Michael Best
// License: MIT (http://www.opensource.org/licenses/mit-license.php)
// Version 2.1.0

(function(factory) {
    if (typeof define === 'function' && define.amd) {
        // [1] AMD anonymous module
        define(['knockout'], factory);
    } else if (typeof exports === 'object') {
        // [2] commonJS
        factory(require('knockout'));
    } else {
        // [3] No module loader (plain <script> tag) - put directly in global namespace
        factory(window.ko);
    }
})(function(ko) {

if (!ko.virtualElements)
    throw Error('Repeat requires at least Knockout 2.1');

var ko_bindingFlags = ko.bindingFlags || {};
var ko_unwrap = ko.utils.unwrapObservable;

var koProtoName = '__ko_proto__';

if (ko.version >= "3.0.0") {
    // In Knockout 3.0.0, use the node preprocessor to replace a node with a repeat binding with a virtual element
    var provider = ko.bindingProvider.instance, previousPreprocessFn = provider.preprocessNode;
    provider.preprocessNode = function(node) {
        var newNodes, nodeBinding;
        if (!previousPreprocessFn || !(newNodes = previousPreprocessFn.call(this, node))) {
            if (node.nodeType === 1 && (nodeBinding = node.getAttribute('data-bind'))) {
                if (/^\s*repeat\s*:/.test(nodeBinding)) {
                    var leadingComment = node.ownerDocument.createComment('ko ' + nodeBinding),
                        trailingComment = node.ownerDocument.createComment('/ko');
                    node.parentNode.insertBefore(leadingComment, node);
                    node.parentNode.insertBefore(trailingComment, node.nextSibling);
                    node.removeAttribute('data-bind');
                    newNodes = [leadingComment, node, trailingComment];
                }
            }
        }
        return newNodes;
    };
}

ko.virtualElements.allowedBindings.repeat = true;
ko.bindingHandlers.repeat = {
    flags: ko_bindingFlags.contentBind | ko_bindingFlags.canUseVirtual,
    init: function(element, valueAccessor, allBindingsAccessor, xxx, bindingContext) {

        // Read and set fixed options--these options cannot be changed
        var repeatParam = ko_unwrap(valueAccessor());
        if (repeatParam && typeof repeatParam == 'object' && !('length' in repeatParam)) {
            var repeatIndex = repeatParam.index,
                repeatData = repeatParam.item,
                repeatStep = repeatParam.step,
                repeatReversed = repeatParam.reverse,
                repeatBind = repeatParam.bind,
                repeatInit = repeatParam.init,
                repeatUpdate = repeatParam.update;
        }
        // Set default values for options that need it
        repeatIndex = repeatIndex || '$index';
        repeatData = repeatData || ko.bindingHandlers.repeat.itemName || '$item';
        repeatStep = repeatStep || 1;
        repeatReversed = repeatReversed || false;

        var parent = element.parentNode, placeholder;
        if (element.nodeType == 8) {    // virtual element
            // Extract the "children" and find the single element node
            var childNodes = ko.utils.arrayFilter(ko.virtualElements.childNodes(element), function(node) { return node.nodeType == 1;});
            if (childNodes.length !== 1) {
                throw Error("Repeat binding requires a single element to repeat");
            }
            ko.virtualElements.emptyNode(element);

            // The placeholder is the closing comment normally, or the opening comment if reversed
            placeholder = repeatReversed ? element : element.nextSibling;
            // The element to repeat is the contained element
            element = childNodes[0];
        } else {    // regular element
            // First clean the element node and remove node's binding
            var origBindString = element.getAttribute('data-bind');
            ko.cleanNode(element);
            element.removeAttribute('data-bind');

            // Original element is no longer needed: delete it and create a placeholder comment
            placeholder = element.ownerDocument.createComment('ko_repeatplaceholder ' + origBindString);
            parent.replaceChild(placeholder, element);
        }

        // extract and remove a data-repeat-bind attribute, if present
        if (!repeatBind) {
            repeatBind = element.getAttribute('data-repeat-bind');
            if (repeatBind) {
                element.removeAttribute('data-repeat-bind');
            }
        }

        // Make a copy of the element node to be copied for each repetition
        var cleanNode = element.cloneNode(true);
        if (typeof repeatBind == "string") {
            cleanNode.setAttribute('data-bind', repeatBind);
            repeatBind = null;
        }

        // Set up persistent data
        var lastRepeatCount = 0,
            notificationObservable = ko.observable(),
            repeatArray, arrayObservable;

        if (repeatInit) {
            repeatInit(parent);
        }

        var subscribable = ko.computed(function() {
            function makeArrayItemAccessor(index) {
                var f = function(newValue) {
                    var item = repeatArray[index];
                    // Reading the value of the item
                    if (!arguments.length) {
                        notificationObservable();   // for dependency tracking
                        return ko_unwrap(item);
                    }
                    // Writing a value to the item
                    if (ko.isObservable(item)) {
                        item(newValue);
                    } else if (arrayObservable && arrayObservable.splice) {
                        arrayObservable.splice(index, 1, newValue);
                    } else {
                        repeatArray[index] = newValue;
                    }
                    return this;
                };
                // Pretend that our accessor function is an observable
                f[koProtoName] = ko.observable;
                return f;
            }

            function makeBinding(item, index, context) {
                return repeatArray
                    ? function() { return repeatBind.call(bindingContext.$data, item, index, context); }
                    : function() { return repeatBind.call(bindingContext.$data, index, context); }
            }

            // Read and set up variable options--these options can change and will update the binding
            var paramObservable = valueAccessor(), repeatParam = ko_unwrap(paramObservable), repeatCount = 0;
            if (repeatParam && typeof repeatParam == 'object') {
                if ('length' in repeatParam) {
                    repeatArray = repeatParam;
                    repeatCount = repeatArray.length;
                } else {
                    if ('foreach' in repeatParam) {
                        repeatArray = ko_unwrap(paramObservable = repeatParam.foreach);
                        if (repeatArray && typeof repeatArray == 'object' && 'length' in repeatArray) {
                            repeatCount = repeatArray.length || 0;
                        } else {
                            repeatCount = repeatArray || 0;
                            repeatArray = null;
                        }
                    }
                    // If a count value is provided (>0), always output that number of items
                    if ('count' in repeatParam)
                        repeatCount = ko_unwrap(repeatParam.count) || repeatCount;
                    // If a limit is provided, don't output more than the limit
                    if ('limit' in repeatParam)
                        repeatCount = Math.min(repeatCount, ko_unwrap(repeatParam.limit)) || repeatCount;
                }
                arrayObservable = repeatArray && ko.isObservable(paramObservable) ? paramObservable : null;
            } else {
                repeatCount = repeatParam || 0;
            }

            // Remove nodes from end if array is shorter
            for (; lastRepeatCount > repeatCount; lastRepeatCount-=repeatStep) {
                ko.removeNode(repeatReversed ? placeholder.nextSibling : placeholder.previousSibling);
            }

            // Notify existing nodes of change
            notificationObservable.notifySubscribers();

            // Add nodes to end if array is longer (also initially populates nodes)
            for (; lastRepeatCount < repeatCount; lastRepeatCount+=repeatStep) {
                // Clone node and add to document
                var newNode = cleanNode.cloneNode(true);
                parent.insertBefore(newNode, repeatReversed ? placeholder.nextSibling : placeholder);
                newNode.setAttribute('data-repeat-index', lastRepeatCount);

                // Apply bindings to inserted node
                if (repeatArray && repeatData == '$data') {
                    var newContext = bindingContext.createChildContext(makeArrayItemAccessor(lastRepeatCount));
                } else {
                    var newContext = bindingContext.extend();
                    if (repeatArray)
                        newContext[repeatData] = makeArrayItemAccessor(lastRepeatCount);
                }
                newContext[repeatIndex] = lastRepeatCount;
                if (repeatBind) {
                    var result = ko.applyBindingsToNode(newNode, makeBinding(newContext[repeatData], lastRepeatCount, newContext), newContext, true),
                        shouldBindDescendants = result && result.shouldBindDescendants;
                }
                if (!repeatBind || (result && shouldBindDescendants !== false)) {
                    ko.applyBindings(newContext, newNode);
                }
            }
            if (repeatUpdate) {
                repeatUpdate(parent);
            }
        }, null, {disposeWhenNodeIsRemoved: placeholder});

        return { controlsDescendantBindings: true, subscribable: subscribable };
    }
};
});