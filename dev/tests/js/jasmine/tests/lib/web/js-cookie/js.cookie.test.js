/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define(['js-cookie/js.cookie'], (Cookies) => {
    'use strict';

    describe('js-cookie/js.cookie', () => {
        let lastSetCookie,
            originalCookieDescriptor;

        beforeEach(() => {
            lastSetCookie = '';

            originalCookieDescriptor =
                Object.getOwnPropertyDescriptor(Document.prototype, 'cookie') ||
                Object.getOwnPropertyDescriptor(HTMLDocument.prototype, 'cookie');

            Object.defineProperty(document, 'cookie', {
                configurable: true,
                get: () => lastSetCookie,
                set: (value) => {
                    lastSetCookie = value;
                }
            });
        });

        afterEach(() => {
            if (originalCookieDescriptor) {
                Object.defineProperty(document, 'cookie', originalCookieDescriptor);
            }
        });

        describe('CVE-2026-46625', () => {
            it('should not inject cookie attributes via __proto__ pollution', () => {
                const attackerAttrs = JSON.parse(
                    '{"__proto__":{"secure":"false","domain":"evil.com","samesite":"None","expires":-1}}'
                );

                Cookies.set('session', 'TOKEN', attackerAttrs);

                expect(lastSetCookie).toContain('session=TOKEN');
                expect(lastSetCookie).not.toContain('domain=evil.com');
                expect(lastSetCookie).not.toContain('secure=false');
                expect(lastSetCookie).not.toContain('samesite=None');
            });

            it('should still apply legitimate cookie attributes', () => {
                Cookies.set('session', 'TOKEN', {
                    path: '/checkout',
                    secure: true
                });

                expect(lastSetCookie).toContain('session=TOKEN');
                expect(lastSetCookie).toContain('path=/checkout');
                expect(lastSetCookie).toContain('secure');
            });
        });
    });
});
