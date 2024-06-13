/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define(['prototype'], () => {
    'use strict';

    describe('prototype', () => {
        describe('Array.from', () => {
            it('should be native code', () => {
                expect(Array.from.toString()).toContain('[native code]');
            });
        });
        describe('Element.remove', () => {
            it('should not throw exception when element does not have parent node', () => {
                let element = $(document.createElement('div'));

                expect(() => {
                    element.remove();
                }).not.toThrow();
            });
        });
    });
});
