/**
 * Retrieve an array of ids of checked nodes
 * @return {Array} array of ids of checked nodes
 */
Ext.tree.TreePanel.prototype.getChecked = function(node){
    var checked = [], i;
    if( typeof node == 'undefined' ) {
        //node = this.rootVisible ? this.getRootNode() : this.getRootNode().firstChild;
        node = this.getRootNode();
    }

    if( node.attributes.checked ) {
        checked.push(node.id);
    }
    if( node.childNodes.length ) {
        for( i = 0; i < node.childNodes.length; i++ ) {
            checked = checked.concat( this.getChecked(node.childNodes[i]) );
        }
    }

    return checked;
};

/**
 * @class Ext.tree.CustomUITreeLoader
 * @extends Ext.tree.TreeLoader
 * Overrides createNode to force uiProvider to be an arbitrary TreeNodeUI to save bandwidth
 */
Ext.tree.CustomUITreeLoader = function() {
    Ext.tree.CustomUITreeLoader.superclass.constructor.apply(this, arguments);
};

Ext.extend(Ext.tree.CustomUITreeLoader, Ext.tree.TreeLoader, {
    createNode : function(attr){
        Ext.apply(attr, this.baseAttr || {});

        if(this.applyLoader !== false){
            attr.loader = this;
        }

        if(typeof attr.uiProvider == 'string'){
            attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
        }

        return(attr.leaf ?
            new Ext.tree.TreeNode(attr) :
                new Ext.tree.AsyncTreeNode(attr));
    }
});


/**
 * @class Ext.tree.CheckboxNodeUI
 * @extends Ext.tree.TreeNodeUI
 * Adds a checkbox to all nodes
 */
Ext.tree.CheckboxNodeUI = function() {
    Ext.tree.CheckboxNodeUI.superclass.constructor.apply(this, arguments);
};

Ext.extend(Ext.tree.CheckboxNodeUI, Ext.tree.TreeNodeUI, {
    /**
     * This is virtually identical to Ext.tree.TreeNodeUI.render, modifications are indicated inline
     */
    render : function(bulkRender){
        var n = this.node;
        var targetNode = n.parentNode ?
            n.parentNode.ui.getContainer() : n.ownerTree.container.dom; /* in later svn builds this changes to n.ownerTree.innerCt.dom */
        if(!this.rendered){
            this.rendered = true;
            var a = n.attributes;

            // add some indent caching, this helps performance when rendering a large tree
            this.indentMarkup = "";
            if(n.parentNode){
                this.indentMarkup = n.parentNode.ui.getChildIndent();
            }

            // modification: added checkbox
            var buf = ['<li class="x-tree-node"><div class="x-tree-node-el ', n.attributes.cls,'">',
                '<span class="x-tree-node-indent">',this.indentMarkup,"</span>",
                '<img src="', this.emptyIcon, '" class="x-tree-ec-icon">',
                '<img src="', a.icon || this.emptyIcon, '" class="x-tree-node-icon',(a.icon ? " x-tree-node-inline-icon" : ""),(a.iconCls ? " "+a.iconCls : ""),'" unselectable="on">',
                '<input class="l-tcb" '+ (n.disabled ? 'disabled="disabled" ' : '') +' type="checkbox" ', (a.checked ? "checked>" : '>'),
                '<a hidefocus="on" href="',a.href ? a.href : "#",'" ',
                 a.hrefTarget ? ' target="'+a.hrefTarget+'"' : "", '>',
                 '<span unselectable="on">',n.text,"</span></a></div>",
                '<ul class="x-tree-node-ct" style="display:none;"></ul>',
                "</li>"];

            if(bulkRender !== true && n.nextSibling && n.nextSibling.ui.getEl()){
                this.wrap = Ext.DomHelper.insertHtml("beforeBegin",
                                                            n.nextSibling.ui.getEl(), buf.join(""));
            }else{
                this.wrap = Ext.DomHelper.insertHtml("beforeEnd", targetNode, buf.join(""));
            }
            this.elNode = this.wrap.childNodes[0];
            this.ctNode = this.wrap.childNodes[1];
            var cs = this.elNode.childNodes;
            this.indentNode = cs[0];
            this.ecNode = cs[1];
            this.iconNode = cs[2];
            this.checkbox = cs[3]; // modification: inserted checkbox
            this.anchor = cs[4];
            this.textNode = cs[4].firstChild;
            if(a.qtip){
             if(this.textNode.setAttributeNS){
                 this.textNode.setAttributeNS("ext", "qtip", a.qtip);
                 if(a.qtipTitle){
                     this.textNode.setAttributeNS("ext", "qtitle", a.qtipTitle);
                 }
             }else{
                 this.textNode.setAttribute("ext:qtip", a.qtip);
                 if(a.qtipTitle){
                     this.textNode.setAttribute("ext:qtitle", a.qtipTitle);
                 }
             }
            } else if(a.qtipCfg) {
                a.qtipCfg.target = Ext.id(this.textNode);
                Ext.QuickTips.register(a.qtipCfg);
            }

            this.initEvents();

            // modification: Add additional handlers here to avoid modifying Ext.tree.TreeNodeUI
            Ext.fly(this.checkbox).on('click', this.check.createDelegate(this, [null]));
            n.on('dblclick', function(e) {
                if( this.isLeaf() ) {
                    this.getUI().toggleCheck();
                }
            });

            if(!this.node.expanded){
                this.updateExpandIcon();
            }
        }else{
            if(bulkRender === true) {
                targetNode.appendChild(this.wrap);
            }
        }
    },

    checked : function() {
        return this.checkbox.checked;
    },

    /**
     * Sets a checkbox appropriately.  By default only walks down through child nodes
     * if called with no arguments (onchange event from the checkbox), otherwise
     * it's assumed the call is being made programatically and the correct arguments are provided.
     * @param {Boolean} state true to check the checkbox, false to clear it. (defaults to the opposite of the checkbox.checked)
     * @param {Boolean} descend true to walk through the nodes children and set their checkbox values. (defaults to false)
     */
    check : function(state, descend, bulk) {
        if (this.node.disabled) {
            return;
        }
        var n = this.node;
        var tree = n.getOwnerTree();
        var parentNode = n.parentNode;n
        if( !n.expanded && !n.childrenRendered ) {
            n.expand(false, false, this.check.createDelegate(this, arguments));
        }

        if( typeof bulk == 'undefined' ) {
            bulk = false;
        }
        if( typeof state == 'undefined' || state === null ) {
            state = this.checkbox.checked;
            descend = !state;
            if( state ) {
                n.expand(false, false);
            }
        } else {
            this.checkbox.checked = state;
        }
        n.attributes.checked = state;

        // do we have parents?
        if( parentNode !== null && state ) {
            // if we're checking the box, check it all the way up
            if( parentNode.getUI().check ) {
                //parentNode.getUI().check(state, false, true);
            }
        }
        if( descend && !n.isLeaf() ) {
            var cs = n.childNodes;
      for(var i = 0; i < cs.length; i++) {
        //cs[i].getUI().check(state, true, true);
      }
        }
        if( !bulk ) {
            tree.fireEvent('check', n, state);
        }
    },

    toggleCheck : function(state) {
        this.check(!this.checkbox.checked, true);
    }

});


/**
 * @class Ext.tree.CheckNodeMultiSelectionModel
 * @extends Ext.tree.MultiSelectionModel
 * Multi selection for a TreePanel containing Ext.tree.CheckboxNodeUI.
 * Adds enhanced selection routines for selecting multiple items
 * and key processing to check/clear checkboxes.
 */
Ext.tree.CheckNodeMultiSelectionModel = function(){
   Ext.tree.CheckNodeMultiSelectionModel.superclass.constructor.call(this);
};

Ext.extend(Ext.tree.CheckNodeMultiSelectionModel, Ext.tree.MultiSelectionModel, {
    init : function(tree){
        this.tree = tree;
        tree.el.on("keydown", this.onKeyDown, this);
        tree.on("click", this.onNodeClick, this);
    },

    /**
     * Handle a node click
     * If ctrl key is down and node is selected will unselect the node.
     * If the shift key is down it will create a contiguous selection
     * (see {@link Ext.tree.CheckNodeMultiSelectionModel#extendSelection} for the limitations)
     */
    onNodeClick : function(node, e){
        if (node.disabled) {
            return;
        }
        if( e.shiftKey && this.extendSelection(node) ) {
            return true;
        }
        if( e.ctrlKey && this.isSelected(node) ) {
            this.unselect(node);
        } else {
            this.select(node, e, e.ctrlKey);
        }
    },

    /**
     * Selects all nodes between the previously selected node and the one that the user has just selected.
     * Will not span multiple depths, so only children of the same parent will be selected.
     */
    extendSelection : function(node) {
        var last = this.lastSelNode;
        if( node == last || !last ) {
            return false; /* same selection, process normally normally */
        }

        if( node.parentNode == last.parentNode ) {
            var cs = node.parentNode.childNodes;
            var i = 0, attr='id', selecting=false, lastSelect=false;
            this.clearSelections(true);
            for( i = 0; i < cs.length; i++ ) {
                // We have to traverse the entire tree b/c don't know of a way to find
                // a numerical representation of a nodes position in a tree.
                if( cs[i].attributes[attr] == last.attributes[attr] || cs[i].attributes[attr] == node.attributes[attr] ) {
                    // lastSelect ensures that we select the final node in the list
                    lastSelect = selecting;
                    selecting = !selecting;
                }
                if( selecting || lastSelect ) {
                    this.select(cs[i], null, true);
                    // if we're selecting the last node break to avoid traversing the entire tree
                    if( lastSelect ) {
                        break;
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    },

    /**
     * Traps the press of the SPACE bar and sets the check state of selected nodes to the opposite state of
     * the selected or last selected node.  Assume you have the following five Ext.tree.CheckboxNodeUIs:
     * [X] One, [X] Two, [X] Three, [ ] Four, [ ] Five
     * If you select them in this order: One, Two, Three, Four, Five and press the space bar they all
     * will be <b>checked</b> (the opposite of the checkbox state of Five).
     * If you select them in this order: Five, Four, Three, Two, One and press the space bar they all
     * will be <b>unchecked</b> which is the opposite of the checkbox state of One.
     */
    onKeyDown : Ext.tree.DefaultSelectionModel.prototype.onKeyDown.createInterceptor(function(e) {
        var s = this.selNode || this.lastSelNode;
        // undesirable, but required
        var sm = this;
        if(!s){
            return;
        }
        var k = e.getKey();
        switch(k){
                case e.SPACE:
                    e.stopEvent();
                    var sel = this.getSelectedNodes();
                    var state = !s.getUI().checked();
                    if( sel.length == 1 ) {
                        s.getUI().check(state, !s.isLeaf());
                    } else {
                        for( var i = 0; i < sel.length; i++ ) {
                            sel[i].getUI().check(state, !sel[i].isLeaf() );
                        }
                    }
            break;
        }

        return true;
    })
});
