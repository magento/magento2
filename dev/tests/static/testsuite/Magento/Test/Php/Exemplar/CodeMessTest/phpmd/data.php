<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
return [
    'cyclomatic complexity' => [
        __DIR__ . '/input/cyclomatic_complexity.php',
        'file/violation[@beginline=8 and @endline=40 and @rule="CyclomaticComplexity" and @ruleset="Code Size Rules"
                    and @package="+global" and @class="Foo" and @method="bar" and @priority=3]',
    ],
    'method length' => [
        __DIR__ . '/input/method_length.php',
        'file/violation[@beginline=8 and @endline=107 and @rule="ExcessiveMethodLength" and @ruleset="Code Size Rules"
                    and @package="+global" and @class="Foo" and @method="bar" and @priority=3]',
    ],
    'parameter list' => [
        __DIR__ . '/input/parameter_list.php',
        'file/violation[@beginline=8 and @endline=11 and @rule="ExcessiveParameterList" and @ruleset="Code Size Rules"
                    and @package="+global" and @class="Foo" and @method="bar" and @priority=3]',
    ],
    'method count' => [
        __DIR__ . '/input/method_count.php',
        'file/violation[@beginline=6 and @endline=116 and @rule="TooManyMethods" and @ruleset="Code Size Rules"
                    and @package="+global" and @class="Foo" and @priority=3]',
    ],
    'field count' => [
        __DIR__ . '/input/field_count.php',
        'file/violation[@beginline=6 and @endline=29 and @rule="TooManyFields" and @ruleset="Code Size Rules"
                    and @package="+global" and @class="Foo" and @priority=3]',
    ],
    'public count' => [
        __DIR__ . '/input/public_count.php',
        'file/violation[@beginline=11 and @endline=58 and @rule="ExcessivePublicCount" and @ruleset="Code Size Rules"
                    and @package="+global" and @class="Foo" and @priority=3]',
    ],
    'prohibited statement' => [
        __DIR__ . '/input/prohibited_statement.php',
        [
            'file/violation[@beginline=7 and @endline=7 and @rule="ExitExpression" and @ruleset="Design Rules"
                        and @priority=1]',
            'file/violation[@beginline=12 and @endline=12 and @rule="EvalExpression" and @ruleset="Design Rules"
                        and @priority=1]'
        ],
    ],
    'prohibited statement goto' => [
        __DIR__ . '/input/prohibited_statement_goto.php',
        'file/violation[@beginline=10 and @endline=10 and @rule="GotoStatement" and @ruleset="Design Rules"
                        and @priority=1]',
    ],
    'inheritance depth' => [
        __DIR__ . '/input/inheritance_depth.php',
        'file/violation[@beginline=15 and @endline=15 and @rule="DepthOfInheritance" and @ruleset="Design Rules"
                    and @package="+global" and @class="Foo07" and @priority=2]',
    ],
    'descendant count' => [
        __DIR__ . '/input/descendant_count.php',
        'file/violation[@beginline=3 and @endline=3 and @rule="NumberOfChildren" and @ruleset="Design Rules"
                    and @package="+global" and @class="Foo01" and @priority=2]',
    ],
    'coupling' => [
        __DIR__ . '/input/coupling.php',
        'file/violation[@beginline=19 and @endline=78 and @rule="CouplingBetweenObjects" and @ruleset="Design Rules"
                    and @package="+global" and @class="Foo" and @priority=2]',
    ],
    'naming' => [
        __DIR__ . '/input/naming.php',
        [
            'file/violation[@beginline=5 and @endline=5 and @rule="ConstantNamingConventions"
                and @ruleset="Naming Rules" and @priority=4]',
            'file/violation[@beginline=11 and @endline=11 and @rule="ShortVariable" and @ruleset="Naming Rules"
                        and @priority=3]',
            'file/violation[@beginline=13 and @endline=13 and @rule="LongVariable" and @ruleset="Naming Rules"
                        and @priority=3]',
            'file/violation[@beginline=18 and @endline=18 and @rule="ConstructorWithNameAsEnclosingClass"
                        and @ruleset="Naming Rules" and @class="Foo" and @method="Foo" and @priority=3]',
            'file/violation[@beginline=20 and @endline=20 and @rule="ShortVariable" and @ruleset="Naming Rules"
                        and @priority=3]',
            'file/violation[@beginline=20 and @endline=20 and @rule="LongVariable" and @ruleset="Naming Rules"
                        and @priority=3]',
            'file/violation[@beginline=22 and @endline=22 and @rule="ShortVariable" and @ruleset="Naming Rules"
                        and @priority=3]',
            'file/violation[@beginline=23 and @endline=23 and @rule="LongVariable" and @ruleset="Naming Rules"
                        and @priority=3]',
            'file/violation[@beginline=30 and @endline=30 and @rule="ShortMethodName" and @ruleset="Naming Rules"
                        and @package="+global" and @class="Foo" and @method="_x" and @priority=3]',
            'file/violation[@beginline=36 and @endline=39 and @rule="BooleanGetMethodName" and @ruleset="Naming Rules"
                        and @package="+global" and @class="Foo" and @method="getBoolValue" and @priority=4]'
        ],
    ],
    'unused' => [
        __DIR__ . '/input/unused.php',
        [
            'file/violation[@beginline=5 and @endline=5 and @rule="UnusedPrivateField" and @ruleset="Unused Code Rules"
                        and @priority=3]',
            'file/violation[@beginline=7 and @endline=7 and @rule="UnusedPrivateMethod" and @ruleset="Unused Code Rules"
                        and @package="+global" and @class="Foo" and @method="_unusedMethod" and @priority=3]',
            'file/violation[@beginline=9 and @endline=9 and @rule="UnusedFormalParameter"
                and @ruleset="Unused Code Rules" and @priority=3]',
            'file/violation[@beginline=11 and @endline=11 and @rule="UnusedLocalVariable"
                and @ruleset="Unused Code Rules" and @priority=3]'
        ],
    ]
];
