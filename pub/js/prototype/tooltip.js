/*
 * Copyright (c) 2006 Jonathan Weiss <jw@innerewut.de>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */


/* tooltip-0.2.js - Small tooltip library on top of Prototype 
 * by Jonathan Weiss <jw@innerewut.de> distributed under the BSD license. 
 *
 * This tooltip library works in two modes. If it gets a valid DOM element 
 * or DOM id as an argument it uses this element as the tooltip. This 
 * element will be placed (and shown) near the mouse pointer when a trigger-
 * element is moused-over.
 * If it gets only a text as an argument instead of a DOM id or DOM element
 * it will create a div with the classname 'tooltip' that holds the given text.
 * This newly created div will be used as the tooltip. This is usefull if you 
 * want to use tooltip.js to create popups out of title attributes.
 * 
 *
 * Usage: 
 *   <script src="/javascripts/prototype.js" type="text/javascript"></script>
 *   <script src="/javascripts/tooltip.js" type="text/javascript"></script>
 *   <script type="text/javascript">
 *     // with valid DOM id
 *     var my_tooltip = new Tooltip('id_of_trigger_element', 'id_of_tooltip_to_show_element')
 *
 *     // with text
 *     var my_other_tooltip = new Tooltip('id_of_trigger_element', 'a nice description')
 *
 *     // create popups for each element with a title attribute
 *    Event.observe(window,"load",function() {
 *      $$("*").findAll(function(node){
 *        return node.getAttribute('title');
 *      }).each(function(node){
 *        new Tooltip(node,node.title);
 *        node.removeAttribute("title");
 *      });
 *    });
 *    
 *   </script>
 * 
 * Now whenever you trigger a mouseOver on the `trigger` element, the tooltip element will
 * be shown. On o mouseOut the tooltip disappears. 
 * 
 * Example:
 * 
 *   <script src="/javascripts/prototype.js" type="text/javascript"></script>
 *   <script src="/javascripts/scriptaculous.js" type="text/javascript"></script>
 *   <script src="/javascripts/tooltip.js" type="text/javascript"></script>
 *
 *   <div id='tooltip' style="display:none; margin: 5px; background-color: red;">
 *     Detail infos on product 1....<br />
 *   </div>
 *
 *   <div id='product_1'>
 *     This is product 1
 *   </div>
 *
 *   <script type="text/javascript">
 *     var my_tooltip = new Tooltip('product_1', 'tooltip')
 *   </script>
 *
 * You can use my_tooltip.destroy() to remove the event observers and thereby the tooltip.
 */

var Tooltip = Class.create();
Tooltip.prototype = {
  initialize: function(element, tool_tip) {
    var options = Object.extend({
      default_css: false,
      margin: "0px",
	    padding: "5px",
	    backgroundColor: "#d6d6fc",
	    min_distance_x: 5,
      min_distance_y: 5,
      delta_x: 0,
      delta_y: 0,
      zindex: 1000
    }, arguments[2] || {});

    this.element      = $(element);

    this.options      = options;
    
    // use the supplied tooltip element or create our own div
    if($(tool_tip)) {
      this.tool_tip = $(tool_tip);
    } else {
      this.tool_tip = $(document.createElement("div")); 
      document.body.appendChild(this.tool_tip);
      this.tool_tip.addClassName("tooltip");
      this.tool_tip.appendChild(document.createTextNode(tool_tip));
    }

    // hide the tool-tip by default
    this.tool_tip.hide();

    this.eventMouseOver = this.showTooltip.bindAsEventListener(this);
    this.eventMouseOut   = this.hideTooltip.bindAsEventListener(this);
    this.eventMouseMove  = this.moveTooltip.bindAsEventListener(this);

    this.registerEvents();
  },

  destroy: function() {
    Event.stopObserving(this.element, "mouseover", this.eventMouseOver);
    Event.stopObserving(this.element, "mouseout", this.eventMouseOut);
    Event.stopObserving(this.element, "mousemove", this.eventMouseMove);
  },

  registerEvents: function() {
    Event.observe(this.element, "mouseover", this.eventMouseOver);
    Event.observe(this.element, "mouseout", this.eventMouseOut);
    Event.observe(this.element, "mousemove", this.eventMouseMove);
  },

  moveTooltip: function(event){
	  Event.stop(event);
	  // get Mouse position
    var mouse_x = Event.pointerX(event);
	  var mouse_y = Event.pointerY(event);
	
	  // decide if wee need to switch sides for the tooltip
	  var dimensions = Element.getDimensions( this.tool_tip );
	  var element_width = dimensions.width;
	  var element_height = dimensions.height;
	
	  if ( (element_width + mouse_x) >= ( this.getWindowWidth() - this.options.min_distance_x) ){ // too big for X
		  mouse_x = mouse_x - element_width;
		  // apply min_distance to make sure that the mouse is not on the tool-tip
		  mouse_x = mouse_x - this.options.min_distance_x;
	  } else {
		  mouse_x = mouse_x + this.options.min_distance_x;
	  }
	
	  if ( (element_height + mouse_y) >= ( this.getWindowHeight() - this.options.min_distance_y) ){ // too big for Y
		  mouse_y = mouse_y - element_height;
	    // apply min_distance to make sure that the mouse is not on the tool-tip
		  mouse_y = mouse_y - this.options.min_distance_y;
	  } else {
		  mouse_y = mouse_y + this.options.min_distance_y;
	  } 
	
	  // now set the right styles
	  this.setStyles(mouse_x, mouse_y);
  },
	
		
  showTooltip: function(event) {
    Event.stop(event);
    this.moveTooltip(event);
	  new Element.show(this.tool_tip);
  },
  
  setStyles: function(x, y){
    // set the right styles to position the tool tip
	  Element.setStyle(this.tool_tip, { position:'absolute',
	 								    top:y + this.options.delta_y + "px",
	 								    left:x + this.options.delta_x + "px",
									    zindex:this.options.zindex
	 								  });
	
	  // apply default theme if wanted
	  if (this.options.default_css){
	  	  Element.setStyle(this.tool_tip, { margin:this.options.margin,
		 		  						                    padding:this.options.padding,
		                                      backgroundColor:this.options.backgroundColor,
										                      zindex:this.options.zindex
		 								    });	
	  }	
  },

  hideTooltip: function(event){
	  new Element.hide(this.tool_tip);
  },

  getWindowHeight: function(){
    var innerHeight;
	  if (navigator.appVersion.indexOf('MSIE')>0) {
		  innerHeight = document.body.clientHeight;
    } else {
		  innerHeight = window.innerHeight;
    }
    return innerHeight;	
  },
 
  getWindowWidth: function(){
    var innerWidth;
	  if (navigator.appVersion.indexOf('MSIE')>0) {
		  innerWidth = document.body.clientWidth;
    } else {
		  innerWidth = window.innerWidth;
    }
    return innerWidth;	
  }

}
