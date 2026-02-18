/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

function showSection(section) {
    document.querySelectorAll('section').forEach(function (element) {
        element.style.display = element.getAttribute('data-section') === section ? null : 'none';
    })
}
