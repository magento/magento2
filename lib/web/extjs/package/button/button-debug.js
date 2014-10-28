/*
 * Ext JS Library 1.1 Beta 1
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */


Ext.Button = function(renderTo, config){
    Ext.apply(this, config);
    this.addEvents({
        
	    "click" : true,
        
	    "toggle" : true,
        
        'mouseover' : true,
        
        'mouseout': true
    });
    if(this.menu){
        this.menu = Ext.menu.MenuMgr.get(this.menu);
    }
    if(renderTo){
        this.render(renderTo);
    }
    Ext.Button.superclass.constructor.call(this);
};

Ext.extend(Ext.Button, Ext.util.Observable, {
    
    hidden : false,
    
    disabled : false,
    
    pressed : false,

    
    tabIndex : undefined,

    
    enableToggle: false,
    
    menu : undefined,
    
    menuAlign : "tl-bl?",

    
    iconCls : undefined,
    
    type : 'button',

    // private
    menuClassTarget: 'tr',

    clickEvent : 'click',
    handleMouseEvents : true,

    
    tooltipType : 'qtip',

    
    
    

    // private
    render : function(renderTo){
        var btn;
        if(this.hideParent){
            this.parentEl = Ext.get(renderTo);
        }
        if(!this.dhconfig){
            if(!this.template){
                if(!Ext.Button.buttonTemplate){
                    // hideous table template
                    Ext.Button.buttonTemplate = new Ext.Template(
                        '<table border="0" cellpadding="0" cellspacing="0" class="x-btn-wrap"><tbody><tr>',
                        '<td class="x-btn-left"><i>&#160;</i></td><td class="x-btn-center"><em unselectable="on"><button class="x-btn-text" type="{1}">{0}</button></em></td><td class="x-btn-right"><i>&#160;</i></td>',
                        "</tr></tbody></table>");
                }
                this.template = Ext.Button.buttonTemplate;
            }
            btn = this.template.append(renderTo, [this.text || '&#160;', this.type], true);
            var btnEl = btn.child("button:first");
            btnEl.on('focus', this.onFocus, this);
            btnEl.on('blur', this.onBlur, this);
            if(this.cls){
                btn.addClass(this.cls);
            }
            if(this.icon){
                btnEl.setStyle('background-image', 'url(' +this.icon +')');
            }
            if(this.iconCls){
                btnEl.addClass(this.iconCls);
                if(!this.cls){
                    btn.addClass(this.text ? 'x-btn-text-icon' : 'x-btn-icon');
                }
            }
            if(this.tabIndex !== undefined){
                btnEl.dom.tabIndex = this.tabIndex;
            }
            if(this.tooltip){
                if(typeof this.tooltip == 'object'){
                    Ext.QuickTips.tips(Ext.apply({
                          target: btnEl.id
                    }, this.tooltip));
                } else {
                    btnEl.dom[this.tooltipType] = this.tooltip;
                }
            }
        }else{
            btn = Ext.DomHelper.append(Ext.get(renderTo).dom, this.dhconfig, true);
        }
        this.el = btn;
        if(this.id){
            this.el.dom.id = this.el.id = this.id;
        }
        if(this.menu){
            this.el.child(this.menuClassTarget).addClass("x-btn-with-menu");
            this.menu.on("show", this.onMenuShow, this);
            this.menu.on("hide", this.onMenuHide, this);
        }
        btn.addClass("x-btn");
        if(Ext.isIE && !Ext.isIE7){
            this.autoWidth.defer(1, this);
        }else{
            this.autoWidth();
        }
        if(this.handleMouseEvents){
            btn.on("mouseover", this.onMouseOver, this);
            btn.on("mouseout", this.onMouseOut, this);
            btn.on("mousedown", this.onMouseDown, this);
        }
        btn.on(this.clickEvent, this.onClick, this);
        //btn.on("mouseup", this.onMouseUp, this);
        if(this.hidden){
            this.hide();
        }
        if(this.disabled){
            this.disable();
        }
        Ext.ButtonToggleMgr.register(this);
        if(this.pressed){
            this.el.addClass("x-btn-pressed");
        }
        if(this.repeat){
            var repeater = new Ext.util.ClickRepeater(btn,
                typeof this.repeat == "object" ? this.repeat : {}
            );
            repeater.on("click", this.onClick,  this);
        }
    },
    
    getEl : function(){
        return this.el;  
    },
    
    
    destroy : function(){
        Ext.ButtonToggleMgr.unregister(this);
        this.el.removeAllListeners();
        this.purgeListeners();
        this.el.remove();
    },

    // private
    autoWidth : function(){
        if(this.el){
            this.el.setWidth("auto");
            if(Ext.isIE7 && Ext.isStrict){
                var ib = this.el.child('button');
                if(ib && ib.getWidth() > 20){
                    ib.clip();
                    ib.setWidth(Ext.util.TextMetrics.measure(ib, this.text).width+ib.getFrameWidth('lr'));
                }
            }
            if(this.minWidth){
                if(this.hidden){
                    this.el.beginMeasure();
                }
                if(this.el.getWidth() < this.minWidth){
                    this.el.setWidth(this.minWidth);
                }
                if(this.hidden){
                    this.el.endMeasure();
                }
            }
        }
    },

    
    setHandler : function(handler, scope){
        this.handler = handler;
        this.scope = scope;  
    },
    
    
    setText : function(text){
        this.text = text;
        if(this.el){
            this.el.child("td.x-btn-center button.x-btn-text").update(text);
        }
        this.autoWidth();
    },
    
    
    getText : function(){
        return this.text;  
    },
    
    
    show: function(){
        this.hidden = false;
        if(this.el){
            this[this.hideParent? 'parentEl' : 'el'].setStyle("display", "");
        }
    },
    
    
    hide: function(){
        this.hidden = true;
        if(this.el){
            this[this.hideParent? 'parentEl' : 'el'].setStyle("display", "none");
        }
    },
    
    
    setVisible: function(visible){
        if(visible) {
            this.show();
        }else{
            this.hide();
        }
    },
    
    
    toggle : function(state){
        state = state === undefined ? !this.pressed : state;
        if(state != this.pressed){
            if(state){
                this.el.addClass("x-btn-pressed");
                this.pressed = true;
                this.fireEvent("toggle", this, true);
            }else{
                this.el.removeClass("x-btn-pressed");
                this.pressed = false;
                this.fireEvent("toggle", this, false);
            }
            if(this.toggleHandler){
                this.toggleHandler.call(this.scope || this, this, state);
            }
        }
    },
    
    
    focus : function(){
        this.el.child('button:first').focus();
    },
    
    
    disable : function(){
        if(this.el){
            this.el.addClass("x-btn-disabled");
        }
        this.disabled = true;
    },
    
    
    enable : function(){
        if(this.el){
            this.el.removeClass("x-btn-disabled");
        }
        this.disabled = false;
    },

    
    setDisabled : function(v){
        this[v !== true ? "enable" : "disable"]();
    },

    // private
    onClick : function(e){
        if(e){
            e.preventDefault();
        }
        if(e.button != 0){
            return;
        }
        if(!this.disabled){
            if(this.enableToggle){
                this.toggle();
            }
            if(this.menu && !this.menu.isVisible()){
                this.menu.show(this.el, this.menuAlign);
            }
            this.fireEvent("click", this, e);
            if(this.handler){
                this.el.removeClass("x-btn-over");
                this.handler.call(this.scope || this, this, e);
            }
        }
    },
    // private
    onMouseOver : function(e){
        if(!this.disabled){
            this.el.addClass("x-btn-over");
            this.fireEvent('mouseover', this, e);
        }
    },
    // private
    onMouseOut : function(e){
        if(!e.within(this.el,  true)){
            this.el.removeClass("x-btn-over");
            this.fireEvent('mouseout', this, e);
        }
    },
    // private
    onFocus : function(e){
        if(!this.disabled){
            this.el.addClass("x-btn-focus");
        }
    },
    // private
    onBlur : function(e){
        this.el.removeClass("x-btn-focus");
    },
    // private
    onMouseDown : function(e){
        if(!this.disabled && e.button == 0){
            this.el.addClass("x-btn-click");
            Ext.get(document).on('mouseup', this.onMouseUp, this);
        }
    },
    // private
    onMouseUp : function(e){
        if(e.button == 0){
            this.el.removeClass("x-btn-click");
            Ext.get(document).un('mouseup', this.onMouseUp, this);
        }
    },
    // private
    onMenuShow : function(e){
        this.el.addClass("x-btn-menu-active");
    },
    // private
    onMenuHide : function(e){
        this.el.removeClass("x-btn-menu-active");
    }   
});

// Private utility class used by Button
Ext.ButtonToggleMgr = function(){
   var groups = {};
   
   function toggleGroup(btn, state){
       if(state){
           var g = groups[btn.toggleGroup];
           for(var i = 0, l = g.length; i < l; i++){
               if(g[i] != btn){
                   g[i].toggle(false);
               }
           }
       }
   }
   
   return {
       register : function(btn){
           if(!btn.toggleGroup){
               return;
           }
           var g = groups[btn.toggleGroup];
           if(!g){
               g = groups[btn.toggleGroup] = [];
           }
           g.push(btn);
           btn.on("toggle", toggleGroup);
       },
       
       unregister : function(btn){
           if(!btn.toggleGroup){
               return;
           }
           var g = groups[btn.toggleGroup];
           if(g){
               g.remove(btn);
               btn.un("toggle", toggleGroup);
           }
       }
   };
}();
