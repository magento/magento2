/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

(function () {
    'use strict';

    var activePopup,
        activeTrigger,
        overlay;

    function closest(element, selector) {
        while (element && element.nodeType === 1) {
            if (element.matches(selector)) {
                return element;
            }

            element = element.parentElement;
        }

        return null;
    }

    function closePopup() {
        if (!activePopup) {
            return;
        }

        activePopup.classList.remove('active');
        activePopup.style.display = 'none';
        activePopup.setAttribute('aria-hidden', 'true');

        if (overlay) {
            overlay.remove();
            overlay = null;
        }

        if (activeTrigger) {
            activeTrigger.setAttribute('aria-expanded', 'false');
            activeTrigger.focus();
        }

        activePopup = null;
        activeTrigger = null;
    }

    function openPopup(trigger) {
        var closeButton,
            target = trigger.getAttribute('data-target');

        closePopup();
        activePopup = target ? document.querySelector(target) : null;

        if (!activePopup) {
            return;
        }

        activeTrigger = trigger;
        activePopup.style.display = 'block';
        activePopup.classList.add('active');
        activePopup.setAttribute('aria-hidden', 'false');
        activeTrigger.setAttribute('aria-expanded', 'true');

        if (trigger.getAttribute('data-backdrop') === 'true') {
            overlay = document.createElement('div');
            overlay.className = 'window overlay active';
            document.body.appendChild(overlay);
        }

        closeButton = activePopup.querySelector('[data-dismiss="popup"]');
        (closeButton || activePopup).focus();
    }

    document.addEventListener('click', function (event) {
        var dismiss = closest(event.target, '[data-dismiss="popup"]'),
            trigger = closest(event.target, '[data-toggle="popup"]');

        if (dismiss || event.target === overlay) {
            event.preventDefault();
            closePopup();
        } else if (trigger) {
            event.preventDefault();
            openPopup(trigger);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closePopup();
        }
    });
}());
