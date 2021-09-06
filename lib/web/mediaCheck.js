window.mediaCheck = function(options) {
    if (typeof window.matchMedia === 'undefined' || !window.matchMedia('!').addListener) {
        throw new Error('No matchMedia support');
    }

    function mqChange(mq, options) {
        if (mq.matches) {
            if (typeof options.entry === 'function') { options.entry(mq); }
        } else {
            if (typeof options.exit === 'function') { options.exit(mq); }
        }
        if (typeof options.both === 'function') { options.both(mq); }
    }

    function createListener() {
        var mq = window.matchMedia(options.media);
        mq.addListener(function () { mqChange(mq, options); });

        window.addEventListener('orientationchange', function () {
            mq = window.matchMedia(options.media);
            mqChange(mq, options);
        }, false);

        mqChange(mq, options);
    }

    createListener();
};
