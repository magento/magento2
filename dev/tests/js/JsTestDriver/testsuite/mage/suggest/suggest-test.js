/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
SuggestTest = TestCase('SuggestTest');
SuggestTest.prototype.setUp = function() {
    /*:DOC += <input name="test-suggest" id="suggest" />*/
    this.suggestElement = jQuery('#suggest');
};
SuggestTest.prototype.tearDown = function() {
    this.suggestDestroy();
};
SuggestTest.prototype.suggestDestroy = function() {
    if(this.suggestElement.data('suggest')) {
        this.suggestElement.suggest('destroy');
    }
};
SuggestTest.prototype.suggestCreate = function(options, element) {
    return (element || this.suggestElement).suggest(options || {} ).data('suggest');
};
SuggestTest.prototype.uiHash = {
    item: {
        id: 1,
        label: 'Test Label'
    }
};

SuggestTest.prototype.testInit = function() {
    this.suggestElement.suggest();
    assertTrue(this.suggestElement.is(':mage-suggest'));
};
SuggestTest.prototype.testCreate = function() {
    var suggestOptions = {
            controls: {
                selector: '.test',
                eventsMap: {
                    focus: ['testfocus'],
                    blur: ['testblur'],
                    select: ['testselect']
                }
            },
            showRecent: true,
            storageKey: 'jsTestDriver-test-suggest-recent',
            multiselect: true
        },
        recentItems = [{
            id: "1",
            "label": "TestLabel1"
        },
        {
            id: "2",
            label: "TestLabel2"
        }],
        setTemplateExecuted,
        prepareValueFieldExecuted,
        renderExecuted,
        bindExecuted;

    if(window.localStorage) {
        localStorage.setItem(suggestOptions.storageKey, JSON.stringify(recentItems));
    }

    var suggestInstance = this.suggestCreate(suggestOptions),
        nonSelectedItem = {id: '', label: ''};

    assertEquals(null, suggestInstance._term);
    assertEquals(suggestInstance._nonSelectedItem, nonSelectedItem);
    assertNull(suggestInstance._renderedContext);
    assertEquals(suggestInstance._selectedItem, nonSelectedItem);
    var control = suggestInstance.options.controls;
    assertEquals(suggestInstance._control, control);
    assertEquals(suggestInstance._recentItems, window.localStorage ? recentItems : []);
    assertTrue(suggestInstance.valueField.is(':hidden'));
    if(window.localStorage) {
        localStorage.removeItem(suggestOptions.storageKey);
    }
};
SuggestTest.prototype.testRender = function() {
    var suggestOptions = {
        dropdownWrapper: '<div class="wrapper-test"></div>',
        className: 'test-suggest',
        inputWrapper: '<div class="test-input-wrapper"></div>'
    };

    var suggestInstance = this.suggestCreate(suggestOptions);
    suggestInstance._render();

    assertTrue(suggestInstance.dropdown.hasClass('wrapper-test'));
    assertTrue(suggestInstance.dropdown.is(':hidden'));
    assertTrue(suggestInstance.element.closest('.test-input-wrapper').size() > 0);
    assertTrue(suggestInstance.element.closest('.' + suggestOptions.className).size() > 0);
    assertEquals(suggestInstance.element.attr('autocomplete'), 'off');

    suggestOptions.appendMethod = 'before';
    this.suggestDestroy();
    suggestInstance = this.suggestCreate(suggestOptions);
    suggestInstance._render();
    assertTrue(suggestInstance.element.prev().is(suggestInstance.dropdown));

    suggestOptions.appendMethod = 'after';
    this.suggestDestroy();
    suggestInstance = this.suggestCreate(suggestOptions);
    suggestInstance._render();
    assertTrue(suggestInstance.element.next().is(suggestInstance.dropdown));
};
SuggestTest.prototype.testCreateValueField = function() {
    var suggestInstance = this.suggestCreate(),
        valueField = suggestInstance._createValueField();
    assertTrue(valueField.is('input'));
    assertTrue(valueField.is(':hidden'));
    this.suggestDestroy();

    suggestInstance = this.suggestCreate({multiselect: true});
    valueField = suggestInstance._createValueField();
    assertTrue(valueField.is('select'));
    assertTrue(valueField.is(':hidden'));
    assertEquals(valueField.attr('multiple'), 'multiple');
};
SuggestTest.prototype.testPrepareValueField = function() {
    var suggestInstance = this.suggestCreate(),
        suggestName = this.suggestElement.attr('name');
    suggestInstance._prepareValueField();

    assertNotUndefined(suggestInstance.valueField);
    assertTrue(suggestInstance.element.prev().is(suggestInstance.valueField));
    assertUndefined(suggestInstance.element.attr('name'));
    assertEquals(suggestInstance.valueField.attr('name'), suggestName);
    this.suggestDestroy();


    var valueField = jQuery('<input id="suggest-single-select-value" type="hidden" />');
    jQuery('body').append(valueField);
    suggestInstance = this.suggestCreate({valueField: '#suggest-single-select-value'});
    assertTrue(suggestInstance.valueField.is(valueField));
};
SuggestTest.prototype.testDestroy = function() {
    var suggestOptions = {
            inputWrapper: '<div class="test-input-wrapper"></div>',
            valueField: null
        },
        suggestInstance = this.suggestCreate(suggestOptions),
        suggestName = suggestInstance.valueField.attr('name');

    assertNotUndefined(suggestInstance.dropdown);
    assertNotUndefined(suggestInstance.valueField);
    assertUndefined(this.suggestElement.attr('name'));

    this.suggestElement.suggest('destroy');

    assertEquals(this.suggestElement.closest('.test-input-wrapper').length, 0);
    assertUndefined(this.suggestElement.attr('autocomplete'));
    assertEquals(this.suggestElement.attr('name'), suggestName);
    assertFalse(suggestInstance.valueField.parents('html').length > 0);
    assertFalse(suggestInstance.dropdown.parents('html').length > 0);
};
SuggestTest.prototype.testValue = function() {
    var value = 'test-value';
    this.suggestElement.val(value);
    jQuery('body').append('<div id="suggest-div">' + value + '</div>');

    var suggestInputInsatnce = this.suggestCreate(),
        suggestDivInsatnce = this.suggestCreate(null, jQuery('#suggest-div'));

    assertEquals(suggestInputInsatnce._value(), value);
    assertEquals(suggestDivInsatnce._value(), value);
};
SuggestTest.prototype.testProxyEvents = function() {
    var fakeEvent = $.extend({}, $.Event('keydown'), {
            ctrlKey: false,
            keyCode: $.ui.keyCode.ENTER,
            which: $.ui.keyCode.ENTER
        }),
        suggestInstance = this.suggestCreate({controls: {selector: null}}),
        ctrlKey,
        keyCode,
        which;

    suggestInstance.dropdown.on('keydown', function(e) {
        ctrlKey = e.ctrlKey;
        keyCode = e.keyCode;
        which = e.which;
    });

    suggestInstance._proxyEvents(fakeEvent);

    assertEquals(ctrlKey, fakeEvent.ctrlKey);
    assertEquals(keyCode, fakeEvent.keyCode);
    assertEquals(which, fakeEvent.which);
};
SuggestTest.prototype.testBind = function() {
    var eventIsBinded = false,
        suggestOptions = {
            events: {
                click: function() {
                    eventIsBinded = true;
                }
            }
        };
    this.suggestCreate(suggestOptions);

    this.suggestElement.trigger('click');
    assertTrue(eventIsBinded);
};
SuggestTest.prototype.testChange = function() {
    var changeIsTriggered,
        suggestInstance = this.suggestCreate();

    suggestInstance._term = 'changed';
    this.suggestElement.on('suggestchange', function(e) {
        changeIsTriggered = true;
    });

    suggestInstance._change($.Event('click'));
    assertTrue(changeIsTriggered);
};
SuggestTest.prototype.testBindDropdown = function() {
    var suggestOptions = {
        controls: {
                eventsMap: {
                    focus: ['testFocus'],
                    blur: ['testBlur'],
                    select: ['testSelect']
                }
            }
        },
        suggestInstance = this.suggestCreate(suggestOptions),
        focusTriggered,
        blurTriggered,
        selectTriggered;

    suggestInstance._onSelectItem = function() {
        selectTriggered = true;
    };
    suggestInstance._focusItem = function() {
        focusTriggered = true;
    };
    suggestInstance._blurItem = function() {
        blurTriggered = true;
    };
    suggestInstance._bindDropdown();

    suggestInstance.dropdown.trigger('testFocus');
    suggestInstance.dropdown.trigger('testBlur');
    suggestInstance.dropdown.trigger('testSelect');

    assertTrue(focusTriggered);
    assertTrue(blurTriggered);
    assertTrue(selectTriggered);
};
SuggestTest.prototype.testTrigger = function() {
    var propogationStopped = true,
        suggestInstance = this.suggestCreate();

    this.suggestElement
        .on('suggesttestevent', function() {
            return false;
        });
    this.suggestElement.parent().on('suggesttestevent', function() {
            propogationStopped = false;
        });
    suggestInstance._trigger('testevent');

    assertTrue(propogationStopped);
};
SuggestTest.prototype.testFocusItem = function() {
    var focusUiParam = false,
        suggestInstance = this.suggestCreate();

    this.suggestElement.on('suggestfocus', function(e, ui) {
        focusUiParam = ui;
    });

    assertUndefined(suggestInstance._focused);
    assertEquals(suggestInstance.element.val(), '');

    suggestInstance._focusItem($.Event('focus'), this.uiHash);
    assertEquals(suggestInstance._focused, this.uiHash.item);
    assertEquals(focusUiParam, this.uiHash);
    assertEquals(suggestInstance.element.val(), this.uiHash.item.label);
};
SuggestTest.prototype.testBlurItem = function() {
    var suggestInstance = this.suggestCreate();

    suggestInstance._focusItem($.Event('focus'), this.uiHash);
    assertEquals(suggestInstance._focused, this.uiHash.item);
    assertEquals(suggestInstance.element.val(), this.uiHash.item.label);

    suggestInstance._blurItem();
    assertNull(suggestInstance._focused);
    //assertEquals(suggestInstance.element.val(), suggestInstance._term.toString());
};
SuggestTest.prototype.testOnSelectItem = function() {
    var item = this.uiHash.item,
        beforeSelect,
        beforeSelectUI,
        beforeSelectPropagationStopped = true,
        select,
        selectUI,
        selectPropagationStopped = true,
        suggestInstance = this.suggestCreate();

    suggestInstance._focused = item;
    this.suggestElement
        .on('suggestbeforeselect', function(e, ui) {
            beforeSelect = true;
            beforeSelectUI = ui;
        })
        .on('suggestselect', function(e, ui) {
            select = true;
            selectUI = ui;
        })
        .parent()
        .on('suggestbeforeselect', function() {
            beforeSelectPropagationStopped = false;
        })
        .on('suggestselect', function() {
            selectPropagationStopped = false;
        });

    suggestInstance._onSelectItem($.Event('select'));

    assertTrue(beforeSelect);
    assertTrue(select);
    assertFalse(beforeSelectPropagationStopped);
    assertFalse(selectPropagationStopped);
    assertEquals(beforeSelectUI.item, item);
    assertEquals(selectUI.item, item);

    beforeSelect = select = beforeSelectUI = selectUI = null;
    beforeSelectPropagationStopped = selectPropagationStopped = true;

    this.suggestElement
        .on('suggestbeforeselect.returnfalse', function(e, ui) {
            return false;
        });

    suggestInstance._focused = item;
    suggestInstance._onSelectItem($.Event('select'));
    assertTrue(beforeSelect);
    assertNull(select);
    assertTrue(beforeSelectPropagationStopped);
    assertTrue(selectPropagationStopped);
    assertEquals(beforeSelectUI.item, item);
    assertNull(selectUI);

    beforeSelect = select = beforeSelectUI = selectUI = null;
    beforeSelectPropagationStopped = selectPropagationStopped = true;

    this.suggestElement
        .off('suggestbeforeselect.returnfalse')
        .on('suggestselect.returnfalse', function() {
            return false;
        });

    suggestInstance._focused = item;
    suggestInstance._onSelectItem($.Event('select'));
    assertTrue(beforeSelect);
    assertTrue(select);
    assertFalse(beforeSelectPropagationStopped);
    assertTrue(selectPropagationStopped);
    assertEquals(beforeSelectUI.item, item);
    assertEquals(selectUI.item, item);

    beforeSelect = select = beforeSelectUI = selectUI = null;
    beforeSelectPropagationStopped = selectPropagationStopped = true;

    this.suggestElement.off('suggestselect.returnfalse');
    var event = $.Event('select');
    event.target = this.suggestElement[0];

    suggestInstance._onSelectItem(event, item);
    assertEquals(suggestInstance._selectedItem, item);
};
SuggestTest.prototype.testSelectItem = function() {
    var suggestInstance = this.suggestCreate();

    suggestInstance._focused = suggestInstance._term = suggestInstance._selectedItem = null;
    suggestInstance.valueField.val('');

    suggestInstance._selectItem($.Event('select'));
    assertNull(suggestInstance._selectedItem);
    assertNull(suggestInstance._term);
    assertEquals(suggestInstance.valueField.val(), '');

    suggestInstance._focused = this.uiHash.item;

    suggestInstance._selectItem($.Event('select'));
    assertEquals(suggestInstance._selectedItem, suggestInstance._focused);
    assertEquals(suggestInstance._term, suggestInstance._focused.label);
    assertEquals(suggestInstance.valueField.val(), suggestInstance._focused.id);
    assertTrue(suggestInstance.dropdown.is(':hidden'));

    this.suggestDestroy();

    var suggestOptions;
    if(window.localStorage) {
        suggestOptions = {
            showRecent: true,
            storageKey: 'jsTestDriver-test-suggest-recent'
        };
        suggestInstance = this.suggestCreate(suggestOptions);
        suggestInstance._focused = this.uiHash.item;

        suggestInstance._selectItem($.Event('select'));

        var storedItem = localStorage.getItem(suggestOptions.storageKey);
        assertEquals(storedItem, JSON.stringify([this.uiHash.item]));
        localStorage.removeItem(suggestOptions.storageKey);
    }
};
SuggestTest.prototype.testSelectItemMultiselect = function() {
    var suggestInstance = this.suggestCreate({multiselect: true});

    suggestInstance._focused = suggestInstance._term = suggestInstance._selectedItem = null;
    suggestInstance.valueField.val('');

    suggestInstance._selectItem($.Event('select'));
    assertNull(suggestInstance._selectedItem);
    assertNull(suggestInstance._term);
    assertFalse(suggestInstance.valueField.find('option').length > 0);
    assertTrue(suggestInstance.dropdown.is(':hidden'));

    suggestInstance._focused = this.uiHash.item;
    var selectedElement = jQuery('<div></div>');
    var event = $.Event('select');
    event.target = selectedElement[0];

    suggestInstance._selectItem(event);
    assertEquals(suggestInstance._selectedItem, suggestInstance._focused);
    assertEquals(suggestInstance._term, '');
    assertTrue(suggestInstance._getOption(suggestInstance._focused).length > 0);
    assertTrue(selectedElement.hasClass(suggestInstance.options.selectedClass));
    assertTrue(suggestInstance.dropdown.is(':hidden'));

    suggestInstance._selectItem(event);
    assertEquals(suggestInstance._selectedItem, suggestInstance._nonSelectedItem);
    assertFalse(suggestInstance._getOption(suggestInstance._focused).length > 0);
    assertFalse(selectedElement.hasClass(suggestInstance.options.selectedClass));
    assertTrue(suggestInstance.dropdown.is(':hidden'));
};
SuggestTest.prototype.testResetSuggestValue = function() {
    var suggestInstance = this.suggestCreate();
    suggestInstance.valueField.val('test');
    suggestInstance._resetSuggestValue();
    assertEquals(suggestInstance.valueField.val(), suggestInstance._nonSelectedItem.id);
};
SuggestTest.prototype.testResetSuggestValueMultiselect = function() {
    var suggestInstance = this.suggestCreate({multiselect: true});
    suggestInstance._focused = this.uiHash.item;
    var selectedElement = jQuery('<div></div>');
    var event = $.Event('select');
    event.target = selectedElement[0];

    suggestInstance._selectItem(event);
    suggestInstance._resetSuggestValue();

    var suggestValue = suggestInstance.valueField.val();
    assertArray(suggestValue);
    assertNotUndefined(suggestValue[0]);
    assertEquals(suggestValue[0], this.uiHash.item.id);
};
SuggestTest.prototype.testReadItemData = function() {
    var testElement = jQuery('<div></div>'),
        suggestInstance = this.suggestCreate();
    assertEquals(suggestInstance._readItemData(testElement), suggestInstance._nonSelectedItem);
    testElement.data('suggestOption', 'test');
    assertEquals(suggestInstance._readItemData(testElement), 'test');
};
SuggestTest.prototype.testIsDropdownShown = function() {
    var suggestInstance = this.suggestCreate();
    suggestInstance.dropdown.hide();
    assertFalse(suggestInstance.isDropdownShown());
    suggestInstance.dropdown.show();
    assertTrue(suggestInstance.isDropdownShown());
};
SuggestTest.prototype.testOpen = function() {
    var openTriggered = false,
        suggestInstance = this.suggestCreate();

    this.suggestElement.on('suggestopen', function() {
        openTriggered = true;
    });

    suggestInstance.dropdown.show();
    suggestInstance.open($.Event('open'));
    assertFalse(openTriggered);

    suggestInstance.dropdown.hide();
    suggestInstance.open($.Event('open'));
    assertTrue(openTriggered);
    assertTrue(suggestInstance.dropdown.is(':visible'));
};
SuggestTest.prototype.testClose = function() {
    var closeTriggered = false,
        suggestInstance = this.suggestCreate();

    suggestInstance.element.val('test');
    suggestInstance._renderedContext = 'test';
    suggestInstance.dropdown.show().append('<div class="test"></div>');

    this.suggestElement.on('suggestclose', function() {
        closeTriggered = true;
    });

    suggestInstance.close($.Event('close'));
    assertNull(suggestInstance._renderedContext);
    assertTrue(suggestInstance.dropdown.is(':hidden'));
    assertFalse(suggestInstance.dropdown.children().length > 0);
    assertTrue(closeTriggered);
};
SuggestTest.prototype.testSetTemplate = function() {
    /*:DOC += <script type="text/template" id="test-template"><div><%= data.test %></div></script>*/
    var suggestInstance = this.suggestCreate({template: '<div><%= data.test %></div>'}),
        tmpl,
        html;

    tmpl = suggestInstance.templates[suggestInstance.templateName]

    html = jQuery('<div />').append(tmpl({
        data: {
            test: 'test'
        }
    })).html();

    assertEquals(html, '<div>test</div>');

    suggestInstance = this.suggestCreate({
        template: '#test-template'
    });

    tmpl = suggestInstance.templates[suggestInstance.templateName];

    html = jQuery('<div />').append(tmpl({
        data: {
            test: 'test'
        }
    })).html();

    assertEquals(html, '<div>test</div>');
};
SuggestTest.prototype.testSearch = function() {
    var searchTriggered = false,
        seachPropagationStopped = true,
        suggestInstance = this.suggestCreate();

    this.suggestElement
        .on('suggestsearch', function() {
            searchTriggered = true;
        })
        .parent()
        .on('suggestsearch', function() {
            seachPropagationStopped = false;
        });

    suggestInstance._term = suggestInstance._value();
    suggestInstance._selectedItem = null;

    suggestInstance.preventBlur = true;
    suggestInstance.search($.Event('search'));

    assertNull(suggestInstance._selectedItem);
    assertFalse(searchTriggered);
    suggestInstance.preventBlur = false;

    this.suggestElement.val('test');
    suggestInstance.search($.Event('search'));

    assertEquals(suggestInstance._term, suggestInstance._value());
    assertTrue(searchTriggered);
    assertFalse(seachPropagationStopped);

    searchTriggered = false;
    seachPropagationStopped = true;
    suggestInstance._selectedItem = null;
    suggestInstance.options.minLength = 10;
    this.suggestElement.val('testtest');

    suggestInstance.search($.Event('search'));

    assertEquals(suggestInstance._selectedItem, suggestInstance._nonSelectedItem);
    assertEquals(suggestInstance.valueField.val(), suggestInstance._selectedItem.id);
    assertFalse(searchTriggered);

    searchTriggered = false;
    seachPropagationStopped = true;
    suggestInstance._selectedItem = null;
    suggestInstance.options.minLength = 1;
    this.suggestElement.val('test');

    this.suggestElement
        .on('suggestsearch.returnfalse', function() {
            return false;
        });

    suggestInstance.search($.Event('search'));

    assertEquals(suggestInstance._term, suggestInstance._value());
    assertTrue(searchTriggered);
    assertTrue(seachPropagationStopped);
};
SuggestTest.prototype.testUderscoreSearch = function() {
    var sourceLaunched = false,
        sorceTerm = null,
        responceExists = false,
        suggestOptions = {
            source: function(term, response){
                sourceLaunched = true;
                sorceTerm = term;
                responceExists = (response && jQuery.type(response) === 'function');
            },
            delay: null
        },
        suggestInstance = this.suggestCreate(suggestOptions);

    suggestInstance._search($.Event('search'), 'test', {});
    assertTrue(sourceLaunched);
    assertEquals(sorceTerm, 'test');
    assertTrue(responceExists);
    assertTrue(this.suggestElement.hasClass(suggestInstance.options.loadingClass));
    assertUndefined(suggestInstance._searchTimeout);

    suggestInstance.options.delay = 100;
    suggestInstance._search($.Event('search'), 'test', {});
    assertNotUndefined(suggestInstance._searchTimeout);
};
SuggestTest.prototype.testPrepareDropdownContext = function() {
    var suggestInstance = this.suggestCreate();

    suggestInstance._items = [this.uiHash.item];
    suggestInstance._term = 'test';
    suggestInstance._selectedItem = this.uiHash.item;

    var context = suggestInstance._prepareDropdownContext({});

    assertEquals(context.items, suggestInstance._items);
    assertEquals(context.term, suggestInstance._term);
    assertEquals(context.optionData(this.uiHash.item),
        'data-suggest-option="' + JSON.stringify(this.uiHash.item).replace(/"/g, '&quot;') + '"');
    assertTrue(context.itemSelected(this.uiHash.item));
    assertNotUndefined(context.noRecordsText);
    assertFalse(context.recentShown());
    assertNotUndefined(context.recentTitle);
    assertNotUndefined(context.showAllTitle);
    assertFalse(context.allShown());
};
SuggestTest.prototype.testIsItemSelected = function() {
    var suggestInstance = this.suggestCreate();
    assertFalse(suggestInstance._isItemSelected(this.uiHash.item));
    suggestInstance._selectedItem = this.uiHash.item;
    assertTrue(suggestInstance._isItemSelected(this.uiHash.item));
    this.suggestDestroy();

    suggestInstance = this.suggestCreate({multiselect: true});
    assertFalse(suggestInstance._isItemSelected(this.uiHash.item));
    suggestInstance.valueField.append('<option value="1">Test Label1</option>');
    assertTrue(suggestInstance._isItemSelected(this.uiHash.item));
};
SuggestTest.prototype.testRenderDropdown = function() {
    var testContext = {
            test: 'test'
        },
        contentUpdatedTriggered = false,
        suggestOptions = {
            template: '<div><%= data.test %></div>'
        },
        suggestInstance = this.suggestCreate(suggestOptions);

    suggestInstance.dropdown.on('contentUpdated', function() {
        contentUpdatedTriggered = true;
    });
    suggestInstance.element.addClass(suggestInstance.options.loadingClass);

    suggestInstance._renderDropdown(null, [this.uiHash.item], testContext);

    assertEquals(suggestInstance._items, [this.uiHash.item]);
    assertEquals(suggestInstance.dropdown.html(), '<div>test</div>');
    assertTrue(contentUpdatedTriggered);
    assertEquals(suggestInstance._renderedContext, suggestInstance._prepareDropdownContext(testContext));
    assertFalse(suggestInstance.element.hasClass(suggestInstance.options.loadingClass));
    assertTrue(suggestInstance.dropdown.is(':visible'));
};
SuggestTest.prototype.testProcessResponse = function() {
    var testContext = {
            test: 'test'
        },
        responseTriggered = false,
        suggestOptions = {
            template: '<div><%= data.test %></div>'
        },
        responcePropagationStopped = true,
        rendererExists,
        responseData,
        suggestInstance = this.suggestCreate(suggestOptions);

    this.suggestElement
        .on('suggestresponse', function(e, data, renderer) {
            responseTriggered = true;
            rendererExists = (renderer && jQuery.type(renderer) === 'function');
            responseData = data;
        })
        .parent()
        .on('suggestresponse', function() {
            responcePropagationStopped = false;
        });
    suggestInstance._processResponse($.Event('response'), [this.uiHash.item], testContext);

    assertTrue(responseTriggered);
    assertTrue(rendererExists);
    assertEquals(responseData, [this.uiHash.item]);
    assertFalse(responcePropagationStopped);
    assertEquals(suggestInstance.dropdown.html(), '<div>test</div>');

    suggestInstance.dropdown.empty();
    this.suggestElement
        .on('suggestresponse.returnfalse', function() {
            return false;
        });
    responcePropagationStopped = true;

    suggestInstance._processResponse($.Event('response'), [this.uiHash.item], testContext);

    assertTrue(responcePropagationStopped);
    assertFalse(suggestInstance.dropdown.children().tength > 0);
};
SuggestTest.prototype.testSource = function() {
    var sourceArray = [this.uiHash.item],
        sourceUrl = 'www.test.url',
        sourceFuncExecuted = false,
        responseExecuted = false,
        responseItems = null,
        sourceFuncTerm = "",
        sourceFuncResponse = null,
        ajaxData = '',
        ajaxUrl = '',
        sourceFunc = function(term, response) {
            sourceFuncExecuted = true;
            sourceFuncTerm = term;
            sourceFuncResponse = (response && jQuery.type(response) === 'function');
        },
        response = function (items) {
            responseExecuted = true;
            responseItems = items;
        };

    var suggestInstance = this.suggestCreate({
        source: sourceArray
    });

    suggestInstance._source('test', response);

    assertTrue(responseExecuted);
    assertEquals(responseItems, sourceArray);
    this.suggestDestroy();

    responseExecuted = false;
    responseItems = null;

    suggestInstance = this.suggestCreate({
        source: sourceUrl,
        ajaxOptions: {
            beforeSend: function(xhr, settings) {
                xhr.abort();
                ajaxData = settings.data;
                ajaxUrl = settings.url;
                settings.success(sourceArray);
            }
        },
        termAjaxArgument: 'test'
    });
    suggestInstance._source('test', response);

    assertTrue(responseExecuted);
    assertEquals(responseItems, sourceArray);
    assertEquals(ajaxData, 'test=test');
    assertEquals(ajaxUrl, sourceUrl);
    this.suggestDestroy();

    responseExecuted = false;
    responseItems = null;

    suggestInstance = this.suggestCreate({
        source: sourceFunc
    });
    suggestInstance._source('test', response);

    assertTrue(sourceFuncExecuted);
    assertEquals(sourceFuncTerm, 'test');
    assertTrue(sourceFuncResponse);
};
SuggestTest.prototype.testAbortSearch = function() {
    var searchAborted = false,
        suggestInstance = this.suggestCreate();

    this.suggestElement.addClass(suggestInstance.options.loadingClass);
    suggestInstance._xhr = {
        abort: function() {
            searchAborted = true;
        }
    };

    suggestInstance._abortSearch();

    assertFalse(this.suggestElement.hasClass(suggestInstance.options.loadingClass));
    assertTrue(searchAborted);
};
SuggestTest.prototype.testShowAll = function() {
    var searchAborted,
        showAllTerm,
        showAllContext,
        suggestInstance = this.suggestCreate();
    suggestInstance._abortSearch = function() {
        searchAborted = true;
    };
    suggestInstance._search = function(e, term, context) {
        showAllTerm = term;
        showAllContext = context;
    };

    suggestInstance._showAll(jQuery.Event('showAll'));

    assertTrue(searchAborted);
    assertEquals(showAllTerm, '');
    assertEquals(showAllContext, {_allShown: true});
};
SuggestTest.prototype.testAddRecent = function() {
    var recentItems = [
            {id: 2, label: 'Test Label 2'},
            {id: 3, label: 'Test Label 3'}
        ],
        suggestInstance = this.suggestCreate();

    suggestInstance._recentItems = recentItems;
    suggestInstance.options.storageKey = 'jsTestDriver-test-suggest-recent';
    suggestInstance._addRecent(this.uiHash.item);

    recentItems.unshift(this.uiHash.item);
    assertEquals(recentItems, suggestInstance._recentItems);
    if(window.localStorage) {
        assertEquals(localStorage.getItem(suggestInstance.options.storageKey), JSON.stringify(recentItems));
    }

    suggestInstance._addRecent(this.uiHash.item);
    assertEquals(recentItems, suggestInstance._recentItems);
    if(window.localStorage) {
        assertEquals(localStorage.getItem(suggestInstance.options.storageKey), JSON.stringify(recentItems));
    }

    suggestInstance.options.storageLimit = 1;
    var newRecentItem = {id: 4, label: 'Test Label 4'};
    suggestInstance._addRecent(newRecentItem);

    assertEquals([newRecentItem], suggestInstance._recentItems);
    if(window.localStorage) {
        assertEquals(localStorage.getItem(suggestInstance.options.storageKey), JSON.stringify([newRecentItem]));
        localStorage.removeItem(suggestInstance.options.storageKey);
    }
};
SuggestTest.prototype.testRenderMultiselect = function() {
    var suggestOptions = {
            multiselect: true,
            multiSuggestWrapper: '<div id="test-multisuggest-wrapper" data-role="parent-choice-element"></div>'
        },
        suggestInstance = this.suggestCreate(suggestOptions);

    assertTrue(this.suggestElement.closest('[data-role="parent-choice-element"]').is('#test-multisuggest-wrapper'));
    assertTrue(suggestInstance.elementWrapper.is('#test-multisuggest-wrapper'));
};
SuggestTest.prototype.testGetOptions = function() {
    var suggestInstance = this.suggestCreate();

    assertFalse(suggestInstance._getOptions().length > 0);

    var option = jQuery('<option></option>');
    suggestInstance.valueField.append(option);
    assertTrue(suggestInstance._getOptions().is(option));
};
SuggestTest.prototype.testFilterSelected = function() {
    var items = [this.uiHash.item, {id: 2, label: 'Test Label2'}],
        suggestInstance = this.suggestCreate();

    suggestInstance.valueField.append('<option value="2">Test Label2</option>');
    assertEquals(suggestInstance._filterSelected(items), [this.uiHash.item]);
};
SuggestTest.prototype.testCreateOption = function() {
    var suggestInstance = this.suggestCreate();

    var option = suggestInstance._createOption(this.uiHash.item);
    assertEquals(option.val(), "1");
    assertEquals(option.prop('selected'), true);
    assertEquals(option.text(), "Test Label");
    assertNotUndefined(option.data('renderedOption'));
};
SuggestTest.prototype.testAddOption = function() {
    var selectTarget = jQuery('<div />'),
        event = jQuery.Event('add'),
        suggestInstance = this.suggestCreate();

    event.target = selectTarget[0];
    suggestInstance._addOption(event, this.uiHash.item);

    var option = suggestInstance.valueField.find('option[value=' + this.uiHash.item.id + ']');
    assertTrue(option.length > 0);
    assertTrue(option.data('selectTarget').is(selectTarget));
};
SuggestTest.prototype.testGetOption = function() {
    var suggestInstance = this.suggestCreate();

    assertFalse(suggestInstance._getOption(this.uiHash.item).length > 0);

    var option = jQuery('<option value="1">Test Label</option>');
    suggestInstance.valueField.append(option);
    assertTrue(suggestInstance._getOption(this.uiHash.item).length > 0);
    assertTrue(suggestInstance._getOption(option).length > 0);
};
SuggestTest.prototype.testRemoveLastAdded = function() {
    var suggestInstance = this.suggestCreate({multiselect: true});

    suggestInstance._addOption({}, this.uiHash.item);
    assertTrue(suggestInstance.valueField.find('option').length > 0);
    suggestInstance._removeLastAdded();
    assertFalse(suggestInstance.valueField.find('option').length > 0);
};
SuggestTest.prototype.testRemoveOption = function() {
    var selectTarget = jQuery('<div />'),
        event = jQuery.Event('select'),
        suggestInstance = this.suggestCreate({multiselect: true});

    selectTarget.addClass(suggestInstance.options.selectedClass);
    event.target = selectTarget[0];

    suggestInstance._addOption(event, this.uiHash.item);
    assertTrue(suggestInstance.valueField.find('option').length > 0);
    suggestInstance.removeOption(event, this.uiHash.item);
    assertFalse(suggestInstance.valueField.find('option').length > 0);
    assertFalse(selectTarget.hasClass(suggestInstance.options.selectedClass));
};
SuggestTest.prototype.testRenderOption = function() {
    var suggestInstance = this.suggestCreate(),
        choiceTmpl;

    suggestInstance.elementWrapper = jQuery('<div />').appendTo('body');

    choiceTmpl = mageTemplate(suggestInstance.options.choiceTemplate, {
        text: this.uiHash.item.label
    });

    var testOption = jQuery(choiceTmpl),
        option = suggestInstance._renderOption(this.uiHash.item);

    assertTrue(option.next().is(suggestInstance.elementWrapper));
    assertEquals(jQuery('<div />').append(testOption).html(), jQuery('<div />').append(option).html());
};
