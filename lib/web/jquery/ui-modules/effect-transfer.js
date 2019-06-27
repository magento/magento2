(function( $, undefined ) {

    $.effects.effect.transfer = function( o, done ) {
        var elem = $( this ),
            target = $( o.to ),
            targetFixed = target.css( "position" ) === "fixed",
            body = $("body"),
            fixTop = targetFixed ? body.scrollTop() : 0,
            fixLeft = targetFixed ? body.scrollLeft() : 0,
            endPosition = target.offset(),
            animation = {
                top: endPosition.top - fixTop ,
                left: endPosition.left - fixLeft ,
                height: target.innerHeight(),
                width: target.innerWidth()
            },
            startPosition = elem.offset(),
            transfer = $( "<div class='ui-effects-transfer'></div>" )
                .appendTo( document.body )
                .addClass( o.className )
                .css({
                    top: startPosition.top - fixTop ,
                    left: startPosition.left - fixLeft ,
                    height: elem.innerHeight(),
                    width: elem.innerWidth(),
                    position: targetFixed ? "fixed" : "absolute"
                })
                .animate( animation, o.duration, o.easing, function() {
                    transfer.remove();
                    done();
                });
    };

})(jQuery);
