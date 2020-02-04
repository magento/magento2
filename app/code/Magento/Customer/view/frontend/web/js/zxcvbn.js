/*
 * Realistic Password Strength Estimator v4.2.0
 *
 * Copyright (c) 2012-2015 Dan Wheeler and Dropbox, Inc.
 *
 * See https://github.com/dropbox/zxcvbn/blob/master/LICENSE.txt
 */
(function (f) {
    if (typeof exports === "object" && typeof module !== "undefined") {
        module.exports = f()
    } else if (typeof define === "function" && define.amd) {
        define([], f)
    } else {
        var g;
        if (typeof window !== "undefined") {
            g = window
        } else if (typeof global !== "undefined") {
            g = global
        } else if (typeof self !== "undefined") {
            g = self
        } else {
            g = this
        }
        g.zxcvbn = f()
    }
})(function () {
    var define, module, exports;
    return (function e(t, n, r) {
        function s(o, u) {
            if (!n[o]) {
                if (!t[o]) {
                    var a = typeof require == "function" && require;
                    if (!u && a)return a(o, !0);
                    if (i)return i(o, !0);
                    var f = new Error("Cannot find module '" + o + "'");
                    throw f.code = "MODULE_NOT_FOUND", f
                }
                var l = n[o] = {exports: {}};
                t[o][0].call(l.exports, function (e) {
                    var n = t[o][1][e];
                    return s(n ? n : e)
                }, l, l.exports, e, t, n, r)
            }
            return n[o].exports
        }

        var i = typeof require == "function" && require;
        for (var o = 0; o < r.length; o++)s(r[o]);
        return s
    })({
        1: [function (require, module, exports) {
            var adjacency_graphs;
            adjacency_graphs = {
                qwerty: {
                    "!": ["`~", null, null, "2@", "qQ", null],
                    '"': [";:", "[{", "]}", null, null, "/?"],
                    "#": ["2@", null, null, "4$", "eE", "wW"],
                    $: ["3#", null, null, "5%", "rR", "eE"],
                    "%": ["4$", null, null, "6^", "tT", "rR"],
                    "&": ["6^", null, null, "8*", "uU", "yY"],
                    "'": [";:", "[{", "]}", null, null, "/?"],
                    "(": ["8*", null, null, "0)", "oO", "iI"],
                    ")": ["9(", null, null, "-_", "pP", "oO"],
                    "*": ["7&", null, null, "9(", "iI", "uU"],
                    "+": ["-_", null, null, null, "]}", "[{"],
                    ",": ["mM", "kK", "lL", ".>", null, null],
                    "-": ["0)", null, null, "=+", "[{", "pP"],
                    ".": [",<", "lL", ";:", "/?", null, null],
                    "/": [".>", ";:", "'\"", null, null, null],
                    0: ["9(", null, null, "-_", "pP", "oO"],
                    1: ["`~", null, null, "2@", "qQ", null],
                    2: ["1!", null, null, "3#", "wW", "qQ"],
                    3: ["2@", null, null, "4$", "eE", "wW"],
                    4: ["3#", null, null, "5%", "rR", "eE"],
                    5: ["4$", null, null, "6^", "tT", "rR"],
                    6: ["5%", null, null, "7&", "yY", "tT"],
                    7: ["6^", null, null, "8*", "uU", "yY"],
                    8: ["7&", null, null, "9(", "iI", "uU"],
                    9: ["8*", null, null, "0)", "oO", "iI"],
                    ":": ["lL", "pP", "[{", "'\"", "/?", ".>"],
                    ";": ["lL", "pP", "[{", "'\"", "/?", ".>"],
                    "<": ["mM", "kK", "lL", ".>", null, null],
                    "=": ["-_", null, null, null, "]}", "[{"],
                    ">": [",<", "lL", ";:", "/?", null, null],
                    "?": [".>", ";:", "'\"", null, null, null],
                    "@": ["1!", null, null, "3#", "wW", "qQ"],
                    A: [null, "qQ", "wW", "sS", "zZ", null],
                    B: ["vV", "gG", "hH", "nN", null, null],
                    C: ["xX", "dD", "fF", "vV", null, null],
                    D: ["sS", "eE", "rR", "fF", "cC", "xX"],
                    E: ["wW", "3#", "4$", "rR", "dD", "sS"],
                    F: ["dD", "rR", "tT", "gG", "vV", "cC"],
                    G: ["fF", "tT", "yY", "hH", "bB", "vV"],
                    H: ["gG", "yY", "uU", "jJ", "nN", "bB"],
                    I: ["uU", "8*", "9(", "oO", "kK", "jJ"],
                    J: ["hH", "uU", "iI", "kK", "mM", "nN"],
                    K: ["jJ", "iI", "oO", "lL", ",<", "mM"],
                    L: ["kK", "oO", "pP", ";:", ".>", ",<"],
                    M: ["nN", "jJ", "kK", ",<", null, null],
                    N: ["bB", "hH", "jJ", "mM", null, null],
                    O: ["iI", "9(", "0)", "pP", "lL", "kK"],
                    P: ["oO", "0)", "-_", "[{", ";:", "lL"],
                    Q: [null, "1!", "2@", "wW", "aA", null],
                    R: ["eE", "4$", "5%", "tT", "fF", "dD"],
                    S: ["aA", "wW", "eE", "dD", "xX", "zZ"],
                    T: ["rR", "5%", "6^", "yY", "gG", "fF"],
                    U: ["yY", "7&", "8*", "iI", "jJ", "hH"],
                    V: ["cC", "fF", "gG", "bB", null, null],
                    W: ["qQ", "2@", "3#", "eE", "sS", "aA"],
                    X: ["zZ", "sS", "dD", "cC", null, null],
                    Y: ["tT", "6^", "7&", "uU", "hH", "gG"],
                    Z: [null, "aA", "sS", "xX", null, null],
                    "[": ["pP", "-_", "=+", "]}", "'\"", ";:"],
                    "\\": ["]}", null, null, null, null, null],
                    "]": ["[{", "=+", null, "\\|", null, "'\""],
                    "^": ["5%", null, null, "7&", "yY", "tT"],
                    _: ["0)", null, null, "=+", "[{", "pP"],
                    "`": [null, null, null, "1!", null, null],
                    a: [null, "qQ", "wW", "sS", "zZ", null],
                    b: ["vV", "gG", "hH", "nN", null, null],
                    c: ["xX", "dD", "fF", "vV", null, null],
                    d: ["sS", "eE", "rR", "fF", "cC", "xX"],
                    e: ["wW", "3#", "4$", "rR", "dD", "sS"],
                    f: ["dD", "rR", "tT", "gG", "vV", "cC"],
                    g: ["fF", "tT", "yY", "hH", "bB", "vV"],
                    h: ["gG", "yY", "uU", "jJ", "nN", "bB"],
                    i: ["uU", "8*", "9(", "oO", "kK", "jJ"],
                    j: ["hH", "uU", "iI", "kK", "mM", "nN"],
                    k: ["jJ", "iI", "oO", "lL", ",<", "mM"],
                    l: ["kK", "oO", "pP", ";:", ".>", ",<"],
                    m: ["nN", "jJ", "kK", ",<", null, null],
                    n: ["bB", "hH", "jJ", "mM", null, null],
                    o: ["iI", "9(", "0)", "pP", "lL", "kK"],
                    p: ["oO", "0)", "-_", "[{", ";:", "lL"],
                    q: [null, "1!", "2@", "wW", "aA", null],
                    r: ["eE", "4$", "5%", "tT", "fF", "dD"],
                    s: ["aA", "wW", "eE", "dD", "xX", "zZ"],
                    t: ["rR", "5%", "6^", "yY", "gG", "fF"],
                    u: ["yY", "7&", "8*", "iI", "jJ", "hH"],
                    v: ["cC", "fF", "gG", "bB", null, null],
                    w: ["qQ", "2@", "3#", "eE", "sS", "aA"],
                    x: ["zZ", "sS", "dD", "cC", null, null],
                    y: ["tT", "6^", "7&", "uU", "hH", "gG"],
                    z: [null, "aA", "sS", "xX", null, null],
                    "{": ["pP", "-_", "=+", "]}", "'\"", ";:"],
                    "|": ["]}", null, null, null, null, null],
                    "}": ["[{", "=+", null, "\\|", null, "'\""],
                    "~": [null, null, null, "1!", null, null]
                },
                dvorak: {
                    "!": ["`~", null, null, "2@", "'\"", null],
                    '"': [null, "1!", "2@", ",<", "aA", null],
                    "#": ["2@", null, null, "4$", ".>", ",<"],
                    $: ["3#", null, null, "5%", "pP", ".>"],
                    "%": ["4$", null, null, "6^", "yY", "pP"],
                    "&": ["6^", null, null, "8*", "gG", "fF"],
                    "'": [null, "1!", "2@", ",<", "aA", null],
                    "(": ["8*", null, null, "0)", "rR", "cC"],
                    ")": ["9(", null, null, "[{", "lL", "rR"],
                    "*": ["7&", null, null, "9(", "cC", "gG"],
                    "+": ["/?", "]}", null, "\\|", null, "-_"],
                    ",": ["'\"", "2@", "3#", ".>", "oO", "aA"],
                    "-": ["sS", "/?", "=+", null, null, "zZ"],
                    ".": [",<", "3#", "4$", "pP", "eE", "oO"],
                    "/": ["lL", "[{", "]}", "=+", "-_", "sS"],
                    0: ["9(", null, null, "[{", "lL", "rR"],
                    1: ["`~", null, null, "2@", "'\"", null],
                    2: ["1!", null, null, "3#", ",<", "'\""],
                    3: ["2@", null, null, "4$", ".>", ",<"],
                    4: ["3#", null, null, "5%", "pP", ".>"],
                    5: ["4$", null, null, "6^", "yY", "pP"],
                    6: ["5%", null, null, "7&", "fF", "yY"],
                    7: ["6^", null, null, "8*", "gG", "fF"],
                    8: ["7&", null, null, "9(", "cC", "gG"],
                    9: ["8*", null, null, "0)", "rR", "cC"],
                    ":": [null, "aA", "oO", "qQ", null, null],
                    ";": [null, "aA", "oO", "qQ", null, null],
                    "<": ["'\"", "2@", "3#", ".>", "oO", "aA"],
                    "=": ["/?", "]}", null, "\\|", null, "-_"],
                    ">": [",<", "3#", "4$", "pP", "eE", "oO"],
                    "?": ["lL", "[{", "]}", "=+", "-_", "sS"],
                    "@": ["1!", null, null, "3#", ",<", "'\""],
                    A: [null, "'\"", ",<", "oO", ";:", null],
                    B: ["xX", "dD", "hH", "mM", null, null],
                    C: ["gG", "8*", "9(", "rR", "tT", "hH"],
                    D: ["iI", "fF", "gG", "hH", "bB", "xX"],
                    E: ["oO", ".>", "pP", "uU", "jJ", "qQ"],
                    F: ["yY", "6^", "7&", "gG", "dD", "iI"],
                    G: ["fF", "7&", "8*", "cC", "hH", "dD"],
                    H: ["dD", "gG", "cC", "tT", "mM", "bB"],
                    I: ["uU", "yY", "fF", "dD", "xX", "kK"],
                    J: ["qQ", "eE", "uU", "kK", null, null],
                    K: ["jJ", "uU", "iI", "xX", null, null],
                    L: ["rR", "0)", "[{", "/?", "sS", "nN"],
                    M: ["bB", "hH", "tT", "wW", null, null],
                    N: ["tT", "rR", "lL", "sS", "vV", "wW"],
                    O: ["aA", ",<", ".>", "eE", "qQ", ";:"],
                    P: [".>", "4$", "5%", "yY", "uU", "eE"],
                    Q: [";:", "oO", "eE", "jJ", null, null],
                    R: ["cC", "9(", "0)", "lL", "nN", "tT"],
                    S: ["nN", "lL", "/?", "-_", "zZ", "vV"],
                    T: ["hH", "cC", "rR", "nN", "wW", "mM"],
                    U: ["eE", "pP", "yY", "iI", "kK", "jJ"],
                    V: ["wW", "nN", "sS", "zZ", null, null],
                    W: ["mM", "tT", "nN", "vV", null, null],
                    X: ["kK", "iI", "dD", "bB", null, null],
                    Y: ["pP", "5%", "6^", "fF", "iI", "uU"],
                    Z: ["vV", "sS", "-_", null, null, null],
                    "[": ["0)", null, null, "]}", "/?", "lL"],
                    "\\": ["=+", null, null, null, null, null],
                    "]": ["[{", null, null, null, "=+", "/?"],
                    "^": ["5%", null, null, "7&", "fF", "yY"],
                    _: ["sS", "/?", "=+", null, null, "zZ"],
                    "`": [null, null, null, "1!", null, null],
                    a: [null, "'\"", ",<", "oO", ";:", null],
                    b: ["xX", "dD", "hH", "mM", null, null],
                    c: ["gG", "8*", "9(", "rR", "tT", "hH"],
                    d: ["iI", "fF", "gG", "hH", "bB", "xX"],
                    e: ["oO", ".>", "pP", "uU", "jJ", "qQ"],
                    f: ["yY", "6^", "7&", "gG", "dD", "iI"],
                    g: ["fF", "7&", "8*", "cC", "hH", "dD"],
                    h: ["dD", "gG", "cC", "tT", "mM", "bB"],
                    i: ["uU", "yY", "fF", "dD", "xX", "kK"],
                    j: ["qQ", "eE", "uU", "kK", null, null],
                    k: ["jJ", "uU", "iI", "xX", null, null],
                    l: ["rR", "0)", "[{", "/?", "sS", "nN"],
                    m: ["bB", "hH", "tT", "wW", null, null],
                    n: ["tT", "rR", "lL", "sS", "vV", "wW"],
                    o: ["aA", ",<", ".>", "eE", "qQ", ";:"],
                    p: [".>", "4$", "5%", "yY", "uU", "eE"],
                    q: [";:", "oO", "eE", "jJ", null, null],
                    r: ["cC", "9(", "0)", "lL", "nN", "tT"],
                    s: ["nN", "lL", "/?", "-_", "zZ", "vV"],
                    t: ["hH", "cC", "rR", "nN", "wW", "mM"],
                    u: ["eE", "pP", "yY", "iI", "kK", "jJ"],
                    v: ["wW", "nN", "sS", "zZ", null, null],
                    w: ["mM", "tT", "nN", "vV", null, null],
                    x: ["kK", "iI", "dD", "bB", null, null],
                    y: ["pP", "5%", "6^", "fF", "iI", "uU"],
                    z: ["vV", "sS", "-_", null, null, null],
                    "{": ["0)", null, null, "]}", "/?", "lL"],
                    "|": ["=+", null, null, null, null, null],
                    "}": ["[{", null, null, null, "=+", "/?"],
                    "~": [null, null, null, "1!", null, null]
                },
                keypad: {
                    "*": ["/", null, null, null, "-", "+", "9", "8"],
                    "+": ["9", "*", "-", null, null, null, null, "6"],
                    "-": ["*", null, null, null, null, null, "+", "9"],
                    ".": ["0", "2", "3", null, null, null, null, null],
                    "/": [null, null, null, null, "*", "9", "8", "7"],
                    0: [null, "1", "2", "3", ".", null, null, null],
                    1: [null, null, "4", "5", "2", "0", null, null],
                    2: ["1", "4", "5", "6", "3", ".", "0", null],
                    3: ["2", "5", "6", null, null, null, ".", "0"],
                    4: [null, null, "7", "8", "5", "2", "1", null],
                    5: ["4", "7", "8", "9", "6", "3", "2", "1"],
                    6: ["5", "8", "9", "+", null, null, "3", "2"],
                    7: [null, null, null, "/", "8", "5", "4", null],
                    8: ["7", null, "/", "*", "9", "6", "5", "4"],
                    9: ["8", "/", "*", "-", "+", null, "6", "5"]
                },
                mac_keypad: {
                    "*": ["/", null, null, null, null, null, "-", "9"],
                    "+": ["6", "9", "-", null, null, null, null, "3"],
                    "-": ["9", "/", "*", null, null, null, "+", "6"],
                    ".": ["0", "2", "3", null, null, null, null, null],
                    "/": ["=", null, null, null, "*", "-", "9", "8"],
                    0: [null, "1", "2", "3", ".", null, null, null],
                    1: [null, null, "4", "5", "2", "0", null, null],
                    2: ["1", "4", "5", "6", "3", ".", "0", null],
                    3: ["2", "5", "6", "+", null, null, ".", "0"],
                    4: [null, null, "7", "8", "5", "2", "1", null],
                    5: ["4", "7", "8", "9", "6", "3", "2", "1"],
                    6: ["5", "8", "9", "-", "+", null, "3", "2"],
                    7: [null, null, null, "=", "8", "5", "4", null],
                    8: ["7", null, "=", "/", "9", "6", "5", "4"],
                    9: ["8", "=", "/", "*", "-", "+", "6", "5"],
                    "=": [null, null, null, null, "/", "9", "8", "7"]
                }
            }, module.exports = adjacency_graphs;

        }, {}],
        2: [function (require, module, exports) {
            var feedback, scoring;
            scoring = require("./scoring"), feedback = {
                default_feedback: {
                    warning: "",
                    suggestions: ["Use a few words, avoid common phrases", "No need for symbols, digits, or uppercase letters"]
                }, get_feedback: function (e, s) {
                    var a, t, r, n, o, i;
                    if (0 === s.length)return this.default_feedback;
                    if (e > 2)return {warning: "", suggestions: []};
                    for (n = s[0], i = s.slice(1), t = 0, r = i.length; r > t; t++)o = i[t], o.token.length > n.token.length && (n = o);
                    return feedback = this.get_match_feedback(n, 1 === s.length), a = "Add another word or two. Uncommon words are better.", null != feedback ? (feedback.suggestions.unshift(a), null == feedback.warning && (feedback.warning = "")) : feedback = {
                        warning: "",
                        suggestions: [a]
                    }, feedback
                }, get_match_feedback: function (e, s) {
                    var a, t;
                    switch (e.pattern) {
                        case"dictionary":
                            return this.get_dictionary_match_feedback(e, s);
                        case"spatial":
                            return a = e.graph.toUpperCase(), t = 1 === e.turns ? "Straight rows of keys are easy to guess" : "Short keyboard patterns are easy to guess", {
                                warning: t,
                                suggestions: ["Use a longer keyboard pattern with more turns"]
                            };
                        case"repeat":
                            return t = 1 === e.base_token.length ? 'Repeats like "aaa" are easy to guess' : 'Repeats like "abcabcabc" are only slightly harder to guess than "abc"', {
                                warning: t,
                                suggestions: ["Avoid repeated words and characters"]
                            };
                        case"sequence":
                            return {
                                warning: "Sequences like abc or 6543 are easy to guess",
                                suggestions: ["Avoid sequences"]
                            };
                        case"regex":
                            if ("recent_year" === e.regex_name)return {
                                warning: "Recent years are easy to guess",
                                suggestions: ["Avoid recent years", "Avoid years that are associated with you"]
                            };
                            break;
                        case"date":
                            return {
                                warning: "Dates are often easy to guess",
                                suggestions: ["Avoid dates and years that are associated with you"]
                            }
                    }
                }, get_dictionary_match_feedback: function (e, s) {
                    var a, t, r, n, o;
                    return n = "passwords" === e.dictionary_name ? !s || e.l33t || e.reversed ? e.guesses_log10 <= 4 ? "This is similar to a commonly used password" : void 0 : e.rank <= 10 ? "This is a top-10 common password" : e.rank <= 100 ? "This is a top-100 common password" : "This is a very common password" : "english" === e.dictionary_name ? s ? "A word by itself is easy to guess" : void 0 : "surnames" === (a = e.dictionary_name) || "male_names" === a || "female_names" === a ? s ? "Names and surnames by themselves are easy to guess" : "Common names and surnames are easy to guess" : "", r = [], o = e.token, o.match(scoring.START_UPPER) ? r.push("Capitalization doesn't help very much") : o.match(scoring.ALL_UPPER) && r.push("All-uppercase is almost as easy to guess as all-lowercase"), e.reversed && e.token.length >= 4 && r.push("Reversed words aren't much harder to guess"), e.l33t && r.push("Predictable substitutions like '@' instead of 'a' don't help very much"), t = {
                        warning: n,
                        suggestions: r
                    }
                }
            }, module.exports = feedback;

        }, {"./scoring": 6}],
        3: [function (require, module, exports) {
            var frequency_lists;
            frequency_lists = {
            }, module.exports = frequency_lists;

        }, {}],
        4: [function (require, module, exports) {
            var feedback, matching, scoring, time, time_estimates, zxcvbn;
            matching = require("./matching"), scoring = require("./scoring"), time_estimates = require("./time_estimates"), feedback = require("./feedback"), time = function () {
                return (new Date).getTime()
            }, zxcvbn = function (e, t) {
                var i, n, c, s, a, r, m, o, u, g, _;
                for (null == t && (t = []), g = time(), u = [], c = 0, s = t.length; s > c; c++)i = t[c], ("string" == (m = typeof i) || "number" === m || "boolean" === m) && u.push(i.toString().toLowerCase());
                matching.set_user_input_dictionary(u), a = matching.omnimatch(e), o = scoring.most_guessable_match_sequence(e, a), o.calc_time = time() - g, n = time_estimates.estimate_attack_times(o.guesses);
                for (r in n)_ = n[r], o[r] = _;
                return o.feedback = feedback.get_feedback(o.score, o.sequence), o
            }, module.exports = zxcvbn;

        }, {"./feedback": 2, "./matching": 5, "./scoring": 6, "./time_estimates": 7}],
        5: [function (require, module, exports) {
            var DATE_MAX_YEAR, DATE_MIN_YEAR, DATE_SPLITS, GRAPHS, L33T_TABLE, RANKED_DICTIONARIES, REGEXEN, SEQUENCES, adjacency_graphs, build_ranked_dict, frequency_lists, lst, matching, name, scoring, indexOf = [].indexOf || function (e) {
                    for (var t = 0, n = this.length; n > t; t++)if (t in this && this[t] === e)return t;
                    return -1
                };
            frequency_lists = require("./frequency_lists"), adjacency_graphs = require("./adjacency_graphs"), scoring = require("./scoring"), build_ranked_dict = function (e) {
                var t, n, r, i, a;
                for (i = {}, t = 1, r = 0, n = e.length; n > r; r++)a = e[r], i[a] = t, t += 1;
                return i
            }, RANKED_DICTIONARIES = {};
            for (name in frequency_lists)lst = frequency_lists[name], RANKED_DICTIONARIES[name] = build_ranked_dict(lst);
            GRAPHS = {
                qwerty: adjacency_graphs.qwerty,
                dvorak: adjacency_graphs.dvorak,
                keypad: adjacency_graphs.keypad,
                mac_keypad: adjacency_graphs.mac_keypad
            }, SEQUENCES = {
                lower: "abcdefghijklmnopqrstuvwxyz",
                upper: "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
                digits: "0123456789"
            }, L33T_TABLE = {
                a: ["4", "@"],
                b: ["8"],
                c: ["(", "{", "[", "<"],
                e: ["3"],
                g: ["6", "9"],
                i: ["1", "!", "|"],
                l: ["1", "|", "7"],
                o: ["0"],
                s: ["$", "5"],
                t: ["+", "7"],
                x: ["%"],
                z: ["2"]
            }, REGEXEN = {recent_year: /19\d\d|200\d|201\d/g}, DATE_MAX_YEAR = 2050, DATE_MIN_YEAR = 1e3, DATE_SPLITS = {
                4: [[1, 2], [2, 3]],
                5: [[1, 3], [2, 3]],
                6: [[1, 2], [2, 4], [4, 5]],
                7: [[1, 3], [2, 3], [4, 5], [4, 6]],
                8: [[2, 4], [4, 6]]
            }, matching = {
                empty: function (e) {
                    var t;
                    return 0 === function () {
                            var n;
                            n = [];
                            for (t in e)n.push(t);
                            return n
                        }().length
                },
                extend: function (e, t) {
                    return e.push.apply(e, t)
                },
                translate: function (e, t) {
                    var n;
                    return function () {
                        var r, i, a, s;
                        for (a = e.split(""), s = [], i = 0, r = a.length; r > i; i++)n = a[i], s.push(t[n] || n);
                        return s
                    }().join("")
                },
                mod: function (e, t) {
                    return (e % t + t) % t
                },
                sorted: function (e) {
                    return e.sort(function (e, t) {
                        return e.i - t.i || e.j - t.j
                    })
                },
                omnimatch: function (e) {
                    var t, n, r, i, a;
                    for (i = [], r = [this.dictionary_match, this.reverse_dictionary_match, this.l33t_match, this.spatial_match, this.repeat_match, this.sequence_match, this.regex_match, this.date_match], a = 0, t = r.length; t > a; a++)n = r[a], this.extend(i, n.call(this, e));
                    return this.sorted(i)
                },
                dictionary_match: function (e, t) {
                    var n, r, i, a, s, o, h, c, u, l, _, f, d, g;
                    null == t && (t = RANKED_DICTIONARIES), s = [], a = e.length, c = e.toLowerCase();
                    for (n in t)for (l = t[n], r = o = 0, _ = a; _ >= 0 ? _ > o : o > _; r = _ >= 0 ? ++o : --o)for (i = h = f = r, d = a; d >= f ? d > h : h > d; i = d >= f ? ++h : --h)c.slice(r, +i + 1 || 9e9)in l && (g = c.slice(r, +i + 1 || 9e9), u = l[g], s.push({
                        pattern: "dictionary",
                        i: r,
                        j: i,
                        token: e.slice(r, +i + 1 || 9e9),
                        matched_word: g,
                        rank: u,
                        dictionary_name: n,
                        reversed: !1,
                        l33t: !1
                    }));
                    return this.sorted(s)
                },
                reverse_dictionary_match: function (e, t) {
                    var n, r, i, a, s, o;
                    for (null == t && (t = RANKED_DICTIONARIES), o = e.split("").reverse().join(""), i = this.dictionary_match(o, t), a = 0, n = i.length; n > a; a++)r = i[a], r.token = r.token.split("").reverse().join(""), r.reversed = !0, s = [e.length - 1 - r.j, e.length - 1 - r.i], r.i = s[0], r.j = s[1];
                    return this.sorted(i)
                },
                set_user_input_dictionary: function (e) {
                    return RANKED_DICTIONARIES.user_inputs = build_ranked_dict(e.slice())
                },
                relevant_l33t_subtable: function (e, t) {
                    var n, r, i, a, s, o, h, c, u, l;
                    for (s = {}, o = e.split(""), a = 0, r = o.length; r > a; a++)n = o[a], s[n] = !0;
                    l = {};
                    for (i in t)u = t[i], h = function () {
                        var e, t, n;
                        for (n = [], t = 0, e = u.length; e > t; t++)c = u[t], c in s && n.push(c);
                        return n
                    }(), h.length > 0 && (l[i] = h);
                    return l
                },
                enumerate_l33t_subs: function (e) {
                    var t, n, r, i, a, s, o, h, c, u, l, _, f, d, g;
                    a = function () {
                        var t;
                        t = [];
                        for (i in e)t.push(i);
                        return t
                    }(), g = [[]], n = function (e) {
                        var t, n, r, a, s, o, h, c;
                        for (n = [], s = {}, o = 0, a = e.length; a > o; o++)h = e[o], t = function () {
                            var e, t, n;
                            for (n = [], c = t = 0, e = h.length; e > t; c = ++t)i = h[c], n.push([i, c]);
                            return n
                        }(), t.sort(), r = function () {
                            var e, n, r;
                            for (r = [], c = n = 0, e = t.length; e > n; c = ++n)i = t[c], r.push(i + "," + c);
                            return r
                        }().join("-"), r in s || (s[r] = !0, n.push(h));
                        return n
                    }, r = function (t) {
                        var i, a, s, o, h, c, u, l, _, f, d, p, E, m, y, A;
                        if (t.length) {
                            for (a = t[0], E = t.slice(1), u = [], d = e[a], l = 0, h = d.length; h > l; l++)for (o = d[l], _ = 0, c = g.length; c > _; _++) {
                                for (m = g[_], i = -1, s = f = 0, p = m.length; p >= 0 ? p > f : f > p; s = p >= 0 ? ++f : --f)if (m[s][0] === o) {
                                    i = s;
                                    break
                                }
                                -1 === i ? (A = m.concat([[o, a]]), u.push(A)) : (y = m.slice(0), y.splice(i, 1), y.push([o, a]), u.push(m), u.push(y))
                            }
                            return g = n(u), r(E)
                        }
                    }, r(a), d = [];
                    for (c = 0, o = g.length; o > c; c++) {
                        for (_ = g[c], f = {}, u = 0, h = _.length; h > u; u++)l = _[u], s = l[0], t = l[1], f[s] = t;
                        d.push(f)
                    }
                    return d
                },
                l33t_match: function (e, t, n) {
                    var r, i, a, s, o, h, c, u, l, _, f, d, g, p, E, m;
                    for (null == t && (t = RANKED_DICTIONARIES), null == n && (n = L33T_TABLE), c = [], _ = this.enumerate_l33t_subs(this.relevant_l33t_subtable(e, n)), u = 0, a = _.length; a > u && (d = _[u], !this.empty(d)); u++)for (p = this.translate(e, d), f = this.dictionary_match(p, t), l = 0, s = f.length; s > l; l++)if (o = f[l], E = e.slice(o.i, +o.j + 1 || 9e9), E.toLowerCase() !== o.matched_word) {
                        h = {};
                        for (g in d)r = d[g], -1 !== E.indexOf(g) && (h[g] = r);
                        o.l33t = !0, o.token = E, o.sub = h, o.sub_display = function () {
                            var e;
                            e = [];
                            for (i in h)m = h[i], e.push(i + " -> " + m);
                            return e
                        }().join(", "), c.push(o)
                    }
                    return this.sorted(c.filter(function (e) {
                        return e.token.length > 1
                    }))
                },
                spatial_match: function (e, t) {
                    var n, r, i;
                    null == t && (t = GRAPHS), i = [];
                    for (r in t)n = t[r], this.extend(i, this.spatial_match_helper(e, n, r));
                    return this.sorted(i)
                },
                SHIFTED_RX: /[~!@#$%^&*()_+QWERTYUIOP{}|ASDFGHJKL:"ZXCVBNM<>?]/,
                spatial_match_helper: function (e, t, n) {
                    var r, i, a, s, o, h, c, u, l, _, f, d, g, p, E;
                    for (f = [], c = 0; c < e.length - 1;)for (u = c + 1, l = null, E = 0, p = "qwerty" !== n && "dvorak" !== n || !this.SHIFTED_RX.exec(e.charAt(c)) ? 0 : 1; ;) {
                        if (g = e.charAt(u - 1), o = !1, h = -1, s = -1, i = t[g] || [], u < e.length)for (a = e.charAt(u), d = 0, _ = i.length; _ > d; d++)if (r = i[d], s += 1, r && -1 !== r.indexOf(a)) {
                            o = !0, h = s, 1 === r.indexOf(a) && (p += 1), l !== h && (E += 1, l = h);
                            break
                        }
                        if (!o) {
                            u - c > 2 && f.push({
                                pattern: "spatial",
                                i: c,
                                j: u - 1,
                                token: e.slice(c, u),
                                graph: n,
                                turns: E,
                                shifted_count: p
                            }), c = u;
                            break
                        }
                        u += 1
                    }
                    return f
                },
                repeat_match: function (e) {
                    var t, n, r, i, a, s, o, h, c, u, l, _, f, d, g;
                    for (d = [], a = /(.+)\1+/g, u = /(.+?)\1+/g, l = /^(.+?)\1+$/, c = 0; c < e.length && (a.lastIndex = u.lastIndex = c, s = a.exec(e), _ = u.exec(e), null != s);)s[0].length > _[0].length ? (f = s, i = l.exec(f[0])[1]) : (f = _, i = f[1]), g = [f.index, f.index + f[0].length - 1], o = g[0], h = g[1], t = scoring.most_guessable_match_sequence(i, this.omnimatch(i)), r = t.match_sequence, n = t.guesses, d.push({
                        pattern: "repeat",
                        i: o,
                        j: h,
                        token: f[0],
                        base_token: i,
                        base_guesses: n,
                        base_matches: r,
                        repeat_count: f[0].length / i.length
                    }), c = h + 1;
                    return d
                },
                sequence_match: function (e) {
                    var t, n, r, i, a, s, o, h, c, u, l, _;
                    a = [];
                    for (l in SEQUENCES)for (u = SEQUENCES[l], h = [1, -1], o = 0, i = h.length; i > o; o++)for (t = h[o], n = 0; n < e.length;)if (c = e.charAt(n), indexOf.call(u, c) < 0)n += 1; else {
                        for (r = n + 1, _ = u.indexOf(e.charAt(n)); r < e.length && (s = this.mod(_ + t, u.length), u.indexOf(e.charAt(r)) === s);)r += 1, _ = s;
                        r -= 1, r - n + 1 > 1 && a.push({
                            pattern: "sequence",
                            i: n,
                            j: r,
                            token: e.slice(n, +r + 1 || 9e9),
                            sequence_name: l,
                            sequence_space: u.length,
                            ascending: 1 === t
                        }), n = r + 1
                    }
                    return this.sorted(a)
                },
                regex_match: function (e, t) {
                    var n, r, i, a;
                    null == t && (t = REGEXEN), n = [];
                    for (name in t)for (r = t[name], r.lastIndex = 0; i = r.exec(e);)a = i[0], n.push({
                        pattern: "regex",
                        token: a,
                        i: i.index,
                        j: i.index + i[0].length - 1,
                        regex_name: name,
                        regex_match: i
                    });
                    return this.sorted(n)
                },
                date_match: function (e) {
                    var t, n, r, i, a, s, o, h, c, u, l, _, f, d, g, p, E, m, y, A, v, I, R, x, T, N, k, D, S, j, b, q, C, O;
                    for (_ = [], f = /^\d{4,8}$/, d = /^(\d{1,4})([\s\/\\_.-])(\d{1,2})\2(\d{1,4})$/, s = E = 0, v = e.length - 4; v >= 0 ? v >= E : E >= v; s = v >= 0 ? ++E : --E)for (o = m = I = s + 3, R = s + 7; (R >= I ? R >= m : m >= R) && !(o >= e.length); o = R >= I ? ++m : --m)if (O = e.slice(s, +o + 1 || 9e9), f.exec(O)) {
                        for (r = [], x = DATE_SPLITS[O.length], y = 0, u = x.length; u > y; y++)T = x[y], h = T[0], c = T[1], a = this.map_ints_to_dmy([parseInt(O.slice(0, h)), parseInt(O.slice(h, c)), parseInt(O.slice(c))]), null != a && r.push(a);
                        if (r.length > 0) {
                            for (t = r[0], g = function (e) {
                                return Math.abs(e.year - scoring.REFERENCE_YEAR)
                            }, p = g(r[0]), N = r.slice(1), A = 0, l = N.length; l > A; A++)n = N[A], i = g(n), p > i && (k = [n, i], t = k[0], p = k[1]);
                            _.push({
                                pattern: "date",
                                token: O,
                                i: s,
                                j: o,
                                separator: "",
                                year: t.year,
                                month: t.month,
                                day: t.day
                            })
                        }
                    }
                    for (s = q = 0, D = e.length - 6; D >= 0 ? D >= q : q >= D; s = D >= 0 ? ++q : --q)for (o = C = S = s + 5, j = s + 9; (j >= S ? j >= C : C >= j) && !(o >= e.length); o = j >= S ? ++C : --C)O = e.slice(s, +o + 1 || 9e9), b = d.exec(O), null != b && (a = this.map_ints_to_dmy([parseInt(b[1]), parseInt(b[3]), parseInt(b[4])]), null != a && _.push({
                        pattern: "date",
                        token: O,
                        i: s,
                        j: o,
                        separator: b[2],
                        year: a.year,
                        month: a.month,
                        day: a.day
                    }));
                    return this.sorted(_.filter(function (e) {
                        var t, n, r, i;
                        for (t = !1, i = 0, n = _.length; n > i; i++)if (r = _[i], e !== r && r.i <= e.i && r.j >= e.j) {
                            t = !0;
                            break
                        }
                        return !t
                    }))
                },
                map_ints_to_dmy: function (e) {
                    var t, n, r, i, a, s, o, h, c, u, l, _, f, d, g, p;
                    if (!(e[1] > 31 || e[1] <= 0)) {
                        for (o = 0, h = 0, g = 0, s = 0, r = e.length; r > s; s++) {
                            if (n = e[s], n > 99 && DATE_MIN_YEAR > n || n > DATE_MAX_YEAR)return;
                            n > 31 && (h += 1), n > 12 && (o += 1), 0 >= n && (g += 1)
                        }
                        if (!(h >= 2 || 3 === o || g >= 2)) {
                            for (u = [[e[2], e.slice(0, 2)], [e[0], e.slice(1, 3)]], c = 0, i = u.length; i > c; c++)if (_ = u[c], p = _[0], d = _[1], p >= DATE_MIN_YEAR && DATE_MAX_YEAR >= p)return t = this.map_ints_to_dm(d), null != t ? {
                                year: p,
                                month: t.month,
                                day: t.day
                            } : void 0;
                            for (l = 0, a = u.length; a > l; l++)if (f = u[l], p = f[0], d = f[1], t = this.map_ints_to_dm(d), null != t)return p = this.two_to_four_digit_year(p), {
                                year: p,
                                month: t.month,
                                day: t.day
                            }
                        }
                    }
                },
                map_ints_to_dm: function (e) {
                    var t, n, r, i, a, s;
                    for (a = [e, e.slice().reverse()], i = 0, n = a.length; n > i; i++)if (s = a[i], t = s[0], r = s[1], t >= 1 && 31 >= t && r >= 1 && 12 >= r)return {
                        day: t,
                        month: r
                    }
                },
                two_to_four_digit_year: function (e) {
                    return e > 99 ? e : e > 50 ? e + scoring.REFERENCE_YEAR - 100 : e + scoring.REFERENCE_YEAR
                }
            }, module.exports = matching;

        }, {"./adjacency_graphs": 1, "./frequency_lists": 3, "./scoring": 6}],
        6: [function (require, module, exports) {
            var BRUTEFORCE_CARDINALITY, MIN_GUESSES_BEFORE_GROWING_SEQUENCE, MIN_SUBMATCH_GUESSES_MULTI_CHAR, MIN_SUBMATCH_GUESSES_SINGLE_CHAR, adjacency_graphs, calc_average_degree, k, scoring, v;
            adjacency_graphs = require("./adjacency_graphs"), calc_average_degree = function (e) {
                var t, n, r, s, a, _;
                t = 0;
                for (r in e)a = e[r], t += function () {
                    var e, t, n;
                    for (n = [], t = 0, e = a.length; e > t; t++)s = a[t], s && n.push(s);
                    return n
                }().length;
                return t /= function () {
                    var t;
                    t = [];
                    for (n in e)_ = e[n], t.push(n);
                    return t
                }().length
            }, BRUTEFORCE_CARDINALITY = 10, MIN_GUESSES_BEFORE_GROWING_SEQUENCE = 1e4, MIN_SUBMATCH_GUESSES_SINGLE_CHAR = 10, MIN_SUBMATCH_GUESSES_MULTI_CHAR = 50, scoring = {
                nCk: function (e, t) {
                    var n, r, s, a;
                    if (t > e)return 0;
                    if (0 === t)return 1;
                    for (s = 1, n = r = 1, a = t; a >= 1 ? a >= r : r >= a; n = a >= 1 ? ++r : --r)s *= e, s /= n, e -= 1;
                    return s
                },
                log10: function (e) {
                    return Math.log(e) / Math.log(10)
                },
                log2: function (e) {
                    return Math.log(e) / Math.log(2)
                },
                factorial: function (e) {
                    var t, n, r, s;
                    if (2 > e)return 1;
                    for (t = 1, n = r = 2, s = e; s >= 2 ? s >= r : r >= s; n = s >= 2 ? ++r : --r)t *= n;
                    return t
                },
                most_guessable_match_sequence: function (e, t, n) {
                    var r, s, a, _, i, u, o, h, E, g, c, l, f, A, S, R, p, M, v, I, N, C, U, T, G, d, k, m, O, P, L, y, B, D;
                    for (null == n && (n = !1), C = [], r = [], M = 0, N = null, S = function (t) {
                        return function (t, n) {
                            var r;
                            return r = {pattern: "bruteforce", token: e.slice(t, +n + 1 || 9e9), i: t, j: n}
                        }
                    }(this), D = function (e) {
                        return function (t, r) {
                            var s;
                            return s = e.factorial(r) * t, n || (s += Math.pow(MIN_GUESSES_BEFORE_GROWING_SEQUENCE, r - 1)), s
                        }
                    }(this), c = A = 0, k = e.length; k >= 0 ? k > A : A > k; c = k >= 0 ? ++A : --A)for (r[c] = [], C[c] = [], U = 1 / 0, G = I = 0, m = M; m >= 0 ? m >= I : I >= m; G = m >= 0 ? ++I : --I)for (o = !0, a = c, 0 === G ? (s = 0, v = 1) : "bruteforce" === (null != (O = r[c - 1]) && null != (P = O[G]) ? P.pattern : void 0) ? (s = r[c - 1][G].i, v = G) : null != (null != (L = r[c - 1]) ? L[G] : void 0) ? (s = c, v = G + 1) : o = !1, o && (_ = S(s, a), T = c - _.token.length, i = this.estimate_guesses(_, e), v > 1 && (i *= C[T][v - 1]), u = D(i, v), U > u && (U = u, C[c][v] = i, N = v, M = Math.max(M, v), r[c][v] = _)), d = 0, f = t.length; f > d; d++)if (R = t[d], R.j === c) {
                        if (y = [R.i, R.j], E = y[0], g = y[1], 0 === G) {
                            if (0 !== E)continue
                        } else if (null == (null != (B = C[E - 1]) ? B[G] : void 0))continue;
                        i = this.estimate_guesses(R, e), G > 0 && (i *= C[E - 1][G]), u = D(i, G + 1), U > u && (U = u, C[c][G + 1] = i, N = G + 1, M = Math.max(M, G + 1), r[c][G + 1] = R)
                    }
                    for (p = [], l = N, c = e.length - 1; c >= 0;)R = r[c][l], p.push(R), c = R.i - 1, l -= 1;
                    return p.reverse(), h = 0 === e.length ? 1 : U, {
                        password: e,
                        guesses: h,
                        guesses_log10: this.log10(h),
                        sequence: p
                    }
                },
                estimate_guesses: function (e, t) {
                    var n, r, s;
                    return null != e.guesses ? e.guesses : (s = 1, e.token.length < t.length && (s = 1 === e.token.length ? MIN_SUBMATCH_GUESSES_SINGLE_CHAR : MIN_SUBMATCH_GUESSES_MULTI_CHAR), n = {
                        bruteforce: this.bruteforce_guesses,
                        dictionary: this.dictionary_guesses,
                        spatial: this.spatial_guesses,
                        repeat: this.repeat_guesses,
                        sequence: this.sequence_guesses,
                        regex: this.regex_guesses,
                        date: this.date_guesses
                    }, r = n[e.pattern].call(this, e), e.guesses = Math.max(r, s), e.guesses_log10 = this.log10(e.guesses), e.guesses)
                },
                bruteforce_guesses: function (e) {
                    var t, n;
                    return t = Math.pow(BRUTEFORCE_CARDINALITY, e.token.length), n = 1 === e.token.length ? MIN_SUBMATCH_GUESSES_SINGLE_CHAR + 1 : MIN_SUBMATCH_GUESSES_MULTI_CHAR + 1, Math.max(t, n)
                },
                repeat_guesses: function (e) {
                    return e.base_guesses * e.repeat_count
                },
                sequence_guesses: function (e) {
                    var t, n;
                    return n = e.token.charAt(0), t = "a" === n || "A" === n || "z" === n || "Z" === n || "0" === n || "1" === n || "9" === n ? 4 : n.match(/\d/) ? 10 : 26, e.ascending || (t *= 2), t * e.token.length
                },
                MIN_YEAR_SPACE: 20,
                REFERENCE_YEAR: 2e3,
                regex_guesses: function (e) {
                    var t, n;
                    if (t = {
                            alpha_lower: 26,
                            alpha_upper: 26,
                            alpha: 52,
                            alphanumeric: 62,
                            digits: 10,
                            symbols: 33
                        }, e.regex_name in t)return Math.pow(t[e.regex_name], e.token.length);
                    switch (e.regex_name) {
                        case"recent_year":
                            return n = Math.abs(parseInt(e.regex_match[0]) - this.REFERENCE_YEAR), n = Math.max(n, this.MIN_YEAR_SPACE)
                    }
                },
                date_guesses: function (e) {
                    var t, n;
                    return n = Math.max(Math.abs(e.year - this.REFERENCE_YEAR), this.MIN_YEAR_SPACE), t = 31 * n * 12, e.has_full_year && (t *= 2), e.separator && (t *= 4), t
                },
                KEYBOARD_AVERAGE_DEGREE: calc_average_degree(adjacency_graphs.qwerty),
                KEYPAD_AVERAGE_DEGREE: calc_average_degree(adjacency_graphs.keypad),
                KEYBOARD_STARTING_POSITIONS: function () {
                    var e, t;
                    e = adjacency_graphs.qwerty, t = [];
                    for (k in e)v = e[k], t.push(k);
                    return t
                }().length,
                KEYPAD_STARTING_POSITIONS: function () {
                    var e, t;
                    e = adjacency_graphs.keypad, t = [];
                    for (k in e)v = e[k], t.push(k);
                    return t
                }().length,
                spatial_guesses: function (e) {
                    var t, n, r, s, a, _, i, u, o, h, E, g, c, l, f, A, S, R;
                    for ("qwerty" === (g = e.graph) || "dvorak" === g ? (A = this.KEYBOARD_STARTING_POSITIONS, s = this.KEYBOARD_AVERAGE_DEGREE) : (A = this.KEYPAD_STARTING_POSITIONS, s = this.KEYPAD_AVERAGE_DEGREE), a = 0, t = e.token.length, R = e.turns, _ = u = 2, c = t; c >= 2 ? c >= u : u >= c; _ = c >= 2 ? ++u : --u)for (h = Math.min(R, _ - 1), i = o = 1, l = h; l >= 1 ? l >= o : o >= l; i = l >= 1 ? ++o : --o)a += this.nCk(_ - 1, i - 1) * A * Math.pow(s, i);
                    if (e.shifted_count)if (n = e.shifted_count, r = e.token.length - e.shifted_count, 0 === n || 0 === r)a *= 2; else {
                        for (S = 0, _ = E = 1, f = Math.min(n, r); f >= 1 ? f >= E : E >= f; _ = f >= 1 ? ++E : --E)S += this.nCk(n + r, _);
                        a *= S
                    }
                    return a
                },
                dictionary_guesses: function (e) {
                    var t;
                    return e.base_guesses = e.rank, e.uppercase_variations = this.uppercase_variations(e), e.l33t_variations = this.l33t_variations(e), t = e.reversed && 2 || 1, e.base_guesses * e.uppercase_variations * e.l33t_variations * t
                },
                START_UPPER: /^[A-Z][^A-Z]+$/,
                END_UPPER: /^[^A-Z]+[A-Z]$/,
                ALL_UPPER: /^[^a-z]+$/,
                ALL_LOWER: /^[^A-Z]+$/,
                uppercase_variations: function (e) {
                    var t, n, r, s, a, _, i, u, o, h, E, g;
                    if (g = e.token, g.match(this.ALL_LOWER))return 1;
                    for (u = [this.START_UPPER, this.END_UPPER, this.ALL_UPPER], _ = 0, a = u.length; a > _; _++)if (h = u[_], g.match(h))return 2;
                    for (n = function () {
                        var e, t, n, s;
                        for (n = g.split(""), s = [], t = 0, e = n.length; e > t; t++)r = n[t], r.match(/[A-Z]/) && s.push(r);
                        return s
                    }().length, t = function () {
                        var e, t, n, s;
                        for (n = g.split(""), s = [], t = 0, e = n.length; e > t; t++)r = n[t], r.match(/[a-z]/) && s.push(r);
                        return s
                    }().length, E = 0, s = i = 1, o = Math.min(n, t); o >= 1 ? o >= i : i >= o; s = o >= 1 ? ++i : --i)E += this.nCk(n + t, s);
                    return E
                },
                l33t_variations: function (e) {
                    var t, n, r, s, a, _, i, u, o, h, E, g, c;
                    if (!e.l33t)return 1;
                    c = 1, o = e.sub;
                    for (E in o)if (g = o[E], s = e.token.toLowerCase().split(""), t = function () {
                            var e, t, n;
                            for (n = [], t = 0, e = s.length; e > t; t++)r = s[t], r === E && n.push(r);
                            return n
                        }().length, n = function () {
                            var e, t, n;
                            for (n = [], t = 0, e = s.length; e > t; t++)r = s[t], r === g && n.push(r);
                            return n
                        }().length, 0 === t || 0 === n)c *= 2; else {
                        for (i = Math.min(n, t), u = 0, a = _ = 1, h = i; h >= 1 ? h >= _ : _ >= h; a = h >= 1 ? ++_ : --_)u += this.nCk(n + t, a);
                        c *= u
                    }
                    return c
                }
            }, module.exports = scoring;

        }, {"./adjacency_graphs": 1}],
        7: [function (require, module, exports) {
            var time_estimates;
            time_estimates = {
                estimate_attack_times: function (e) {
                    var t, n, s, o;
                    n = {
                        online_throttling_100_per_hour: e / (100 / 3600),
                        online_no_throttling_10_per_second: e / 100,
                        offline_slow_hashing_1e4_per_second: e / 1e4,
                        offline_fast_hashing_1e10_per_second: e / 1e10
                    }, t = {};
                    for (s in n)o = n[s], t[s] = this.display_time(o);
                    return {crack_times_seconds: n, crack_times_display: t, score: this.guesses_to_score(e)}
                }, guesses_to_score: function (e) {
                    var t;
                    return t = 5, 1e3 + t > e ? 0 : 1e6 + t > e ? 1 : 1e8 + t > e ? 2 : 1e10 + t > e ? 3 : 4
                }, display_time: function (e) {
                    var t, n, s, o, _, r, i, a, u, c;
                    return i = 60, r = 60 * i, s = 24 * r, a = 31 * s, c = 12 * a, n = 100 * c, u = 1 > e ? [null, "less than a second"] : i > e ? (t = Math.round(e), [t, t + " second"]) : r > e ? (t = Math.round(e / i), [t, t + " minute"]) : s > e ? (t = Math.round(e / r), [t, t + " hour"]) : a > e ? (t = Math.round(e / s), [t, t + " day"]) : c > e ? (t = Math.round(e / a), [t, t + " month"]) : n > e ? (t = Math.round(e / c), [t, t + " year"]) : [null, "centuries"], o = u[0], _ = u[1], null != o && 1 !== o && (_ += "s"), _
                }
            }, module.exports = time_estimates;

        }, {}]
    }, {}, [4])(4)
});
//# sourceMappingURL=zxcvbn.js.map
