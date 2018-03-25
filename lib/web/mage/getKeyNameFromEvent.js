/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
], function () {

    /**
     * @param {Event} e The key event
     */
    function getKeyNameFromEvent(e) {
        var keyName = e.key;
        switch (keyName) {
            case "Esc": // Internet Explorer and Edge
                keyName = "Escape";
                break;
            case "Down": // Internet Explorer and Edge
                keyName = "ArrowDown";
                break;
            case "Up": // Internet Explorer and Edge
                keyName = "ArrowUp";
                break;
            case "Left": // Internet Explorer and Edge
                keyName = "ArrowLeft";
                break;
            case "Right": // Internet Explorer and Edge
                keyName = "ArrowRight";
                break;
        }
        return keyName;
    }

    return getKeyNameFromEvent;
});
