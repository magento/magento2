/* ========================================================================
 * Bootstrap: modal.js v3.1.0
 * http://getbootstrap.com/javascript/#modals
 * ========================================================================
 * Copyright 2011-2014 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */


+function ($) {
  'use strict';

  // POPUP CLASS DEFINITION
  // ======================

  var Popup = function (element, options) {
    this.options   = options
    this.$element  = $(element)
    this.$overlay =
    this.isShown   = null

    if (this.options.remote) {
      this.$element
        .find('.popup.content')
        .load(this.options.remote, $.proxy(function () {
          this.$element.trigger('loaded.bs.popup')
        }, this))
    }
  }

  Popup.DEFAULTS = {
    overlay: true,
    keyboard: true,
    show: true
  }

  Popup.prototype.toggle = function (_relatedTarget) {
    return this[!this.isShown ? 'show' : 'hide'](_relatedTarget)
  }

  Popup.prototype.show = function (_relatedTarget) {
    var that = this
    var e    = $.Event('show.bs.popup', { relatedTarget: _relatedTarget })

    this.$element.trigger(e)

    if (this.isShown || e.isDefaultPrevented()) return

    this.isShown = true

    this.escape()

    this.$element.on('click.dismiss.bs.popup', '[data-dismiss="popup"]', $.proxy(this.hide, this))

    this.overlay(function () {
      var transition = $.support.transition && that.$element.hasClass('fade')

      if (!that.$element.parent().length) {
        that.$element.appendTo(document.body) // don't move popups dom position
      }

      that.$element
        .show()
        .scrollTop(0)

      if (transition) {
        that.$element[0].offsetWidth // force reflow
      }

      that.$element
        .addClass('active')
        .attr('aria-hidden', false)

      that.enforceFocus()

      var e = $.Event('shown.bs.popup', { relatedTarget: _relatedTarget })

      // transition ?
      //   that.$element.find('.popup-dialog') // wait for popup to slide in
      //     .one($.support.transition.end, function () {
      //       that.$element.focus().trigger(e)
      //     })
      //     .emulateTransitionEnd(300) :
        that.$element.focus().trigger(e)
    })
  }

  Popup.prototype.hide = function (e) {
    if (e) e.preventDefault()

    e = $.Event('hide.bs.popup')

    this.$element.trigger(e)

    if (!this.isShown || e.isDefaultPrevented()) return

    this.isShown = false

    this.escape()

    $(document).off('focusin.bs.popup')

    this.$element
      .removeClass('active')
      .attr('aria-hidden', true)
      .off('click.dismiss.bs.popup')

    // $.support.transition && this.$element.hasClass('fade') ?
    //   this.$element
    //     .one($.support.transition.end, $.proxy(this.hidePopup, this))
    //     .emulateTransitionEnd(300) :
      this.hidePopup()
  }

  Popup.prototype.enforceFocus = function () {
    $(document)
      .off('focusin.bs.popup') // guard against infinite focus loop
      .on('focusin.bs.popup', $.proxy(function (e) {
        if (this.$element[0] !== e.target && !this.$element.has(e.target).length) {
          this.$element.focus()
        }
      }, this))
  }

  Popup.prototype.escape = function () {
    if (this.isShown && this.options.keyboard) {
      this.$element.on('keyup.dismiss.bs.popup', $.proxy(function (e) {
        e.which == 27 && this.hide()
      }, this))
    } else if (!this.isShown) {
      this.$element.off('keyup.dismiss.bs.popup')
    }
  }

  Popup.prototype.hidePopup = function () {
    var that = this
    this.$element.hide()
    this.overlay(function () {
      that.removeBackdrop()
      that.$element.trigger('hidden.bs.popup')
    })
  }

  Popup.prototype.removeBackdrop = function () {
    this.$overlay && this.$overlay.remove()
    this.$overlay = null
  }

  Popup.prototype.overlay = function (callback) {
    var animate = this.$element.hasClass('fade') ? 'fade' : ''

    if (this.isShown && this.options.overlay) {
      var doAnimate = $.support.transition && animate

      this.$overlay = $('<div class="window overlay ' + animate + '" />')
        .appendTo(document.body)

      this.$element.on('click.dismiss.bs.popup', $.proxy(function (e) {
        if (e.target !== e.currentTarget) return
        this.options.overlay == 'static'
          ? this.$element[0].focus.call(this.$element[0])
          : this.hide.call(this)
      }, this))

      if (doAnimate) this.$overlay[0].offsetWidth // force reflow

      this.$overlay.addClass('active')

      if (!callback) return

      // doAnimate ?
      //   this.$overlay
      //     .one($.support.transition.end, callback)
      //     .emulateTransitionEnd(150) :
        callback()

    } else if (!this.isShown && this.$overlay) {
      this.$overlay.removeClass('active')

      // $.support.transition && this.$element.hasClass('fade') ?
      //   this.$overlay
      //     .one($.support.transition.end, callback)
      //     .emulateTransitionEnd(150) :
        callback()

    } else if (callback) {
      callback()
    }
  }


  // POPUP PLUGIN DEFINITION
  // =======================

  var old = $.fn.popup

  $.fn.popup = function (option, _relatedTarget) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.popup')
      var options = $.extend({}, Popup.DEFAULTS, $this.data(), typeof option == 'object' && option)

      if (!data) $this.data('bs.popup', (data = new Popup(this, options)))
      if (typeof option == 'string') data[option](_relatedTarget)
      else if (options.show) data.show(_relatedTarget)
    })
  }

  $.fn.popup.Constructor = Popup


  // POPUP NO CONFLICT
  // =================

  $.fn.popup.noConflict = function () {
    $.fn.popup = old
    return this
  }


  // POPUP DATA-API
  // ==============

  $(document).on('click.bs.popup.data-api', '[data-toggle="popup"]', function (e) {
    var $this   = $(this)
    var href    = $this.attr('href')
    var $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) //strip for ie7
    var option  = $target.data('bs.popup') ? 'toggle' : $.extend({ remote: !/#/.test(href) && href }, $target.data(), $this.data())

    if ($this.is('a')) e.preventDefault()

    $target
      .popup(option, this)
      .one('hide', function () {
        $this.is(':visible') && $this.focus()
      })
  })

  $(document)
    .on('show.bs.popup', '.popup', function () { $(document.body).addClass('popup-open') })
    .on('hidden.bs.popup', '.popup', function () { $(document.body).removeClass('popup-open') })


}(jQuery);