<?php

/*
 * This file is part of the JSON Lint package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seld\JsonLint;
use stdClass;

/**
 * Parser class
 *
 * Example:
 *
 * $parser = new JsonParser();
 * // returns null if it's valid json, or an error object
 * $parser->lint($json);
 * // returns parsed json, like json_decode does, but slower, throws exceptions on failure.
 * $parser->parse($json);
 *
 * Ported from https://github.com/zaach/jsonlint
 */
class JsonParser
{
    const DETECT_KEY_CONFLICTS = 1;
    const ALLOW_DUPLICATE_KEYS = 2;
    const PARSE_TO_ASSOC = 4;

    private $lexer;

    private $flags;
    private $stack;
    private $vstack; // semantic value stack
    private $lstack; // location stack

    private $symbols = array(
        'error'                 => 2,
        'JSONString'            => 3,
        'STRING'                => 4,
        'JSONNumber'            => 5,
        'NUMBER'                => 6,
        'JSONNullLiteral'       => 7,
        'NULL'                  => 8,
        'JSONBooleanLiteral'    => 9,
        'TRUE'                  => 10,
        'FALSE'                 => 11,
        'JSONText'              => 12,
        'JSONValue'             => 13,
        'EOF'                   => 14,
        'JSONObject'            => 15,
        'JSONArray'             => 16,
        '{'                     => 17,
        '}'                     => 18,
        'JSONMemberList'        => 19,
        'JSONMember'            => 20,
        ':'                     => 21,
        ','                     => 22,
        '['                     => 23,
        ']'                     => 24,
        'JSONElementList'       => 25,
        '$accept'               => 0,
        '$end'                  => 1,
    );

    private $terminals_ = array(
        2   => "error",
        4   => "STRING",
        6   => "NUMBER",
        8   => "NULL",
        10  => "TRUE",
        11  => "FALSE",
        14  => "EOF",
        17  => "{",
        18  => "}",
        21  => ":",
        22  => ",",
        23  => "[",
        24  => "]",
    );

    private $productions_ = array(
        0,
        array(3, 1),
        array(5, 1),
        array(7, 1),
        array(9, 1),
        array(9, 1),
        array(12, 2),
        array(13, 1),
        array(13, 1),
        array(13, 1),
        array(13, 1),
        array(13, 1),
        array(13, 1),
        array(15, 2),
        array(15, 3),
        array(20, 3),
        array(19, 1),
        array(19, 3),
        array(16, 2),
        array(16, 3),
        array(25, 1),
        array(25, 3)
    );

    private $table = array(array(3 => 5, 4 => array(1,12), 5 => 6, 6 => array(1,13), 7 => 3, 8 => array(1,9), 9 => 4, 10 => array(1,10), 11 => array(1,11), 12 => 1, 13 => 2, 15 => 7, 16 => 8, 17 => array(1,14), 23 => array(1,15)), array( 1 => array(3)), array( 14 => array(1,16)), array( 14 => array(2,7), 18 => array(2,7), 22 => array(2,7), 24 => array(2,7)), array( 14 => array(2,8), 18 => array(2,8), 22 => array(2,8), 24 => array(2,8)), array( 14 => array(2,9), 18 => array(2,9), 22 => array(2,9), 24 => array(2,9)), array( 14 => array(2,10), 18 => array(2,10), 22 => array(2,10), 24 => array(2,10)), array( 14 => array(2,11), 18 => array(2,11), 22 => array(2,11), 24 => array(2,11)), array( 14 => array(2,12), 18 => array(2,12), 22 => array(2,12), 24 => array(2,12)), array( 14 => array(2,3), 18 => array(2,3), 22 => array(2,3), 24 => array(2,3)), array( 14 => array(2,4), 18 => array(2,4), 22 => array(2,4), 24 => array(2,4)), array( 14 => array(2,5), 18 => array(2,5), 22 => array(2,5), 24 => array(2,5)), array( 14 => array(2,1), 18 => array(2,1), 21 => array(2,1), 22 => array(2,1), 24 => array(2,1)), array( 14 => array(2,2), 18 => array(2,2), 22 => array(2,2), 24 => array(2,2)), array( 3 => 20, 4 => array(1,12), 18 => array(1,17), 19 => 18, 20 => 19 ), array( 3 => 5, 4 => array(1,12), 5 => 6, 6 => array(1,13), 7 => 3, 8 => array(1,9), 9 => 4, 10 => array(1,10), 11 => array(1,11), 13 => 23, 15 => 7, 16 => 8, 17 => array(1,14), 23 => array(1,15), 24 => array(1,21), 25 => 22 ), array( 1 => array(2,6)), array( 14 => array(2,13), 18 => array(2,13), 22 => array(2,13), 24 => array(2,13)), array( 18 => array(1,24), 22 => array(1,25)), array( 18 => array(2,16), 22 => array(2,16)), array( 21 => array(1,26)), array( 14 => array(2,18), 18 => array(2,18), 22 => array(2,18), 24 => array(2,18)), array( 22 => array(1,28), 24 => array(1,27)), array( 22 => array(2,20), 24 => array(2,20)), array( 14 => array(2,14), 18 => array(2,14), 22 => array(2,14), 24 => array(2,14)), array( 3 => 20, 4 => array(1,12), 20 => 29 ), array( 3 => 5, 4 => array(1,12), 5 => 6, 6 => array(1,13), 7 => 3, 8 => array(1,9), 9 => 4, 10 => array(1,10), 11 => array(1,11), 13 => 30, 15 => 7, 16 => 8, 17 => array(1,14), 23 => array(1,15)), array( 14 => array(2,19), 18 => array(2,19), 22 => array(2,19), 24 => array(2,19)), array( 3 => 5, 4 => array(1,12), 5 => 6, 6 => array(1,13), 7 => 3, 8 => array(1,9), 9 => 4, 10 => array(1,10), 11 => array(1,11), 13 => 31, 15 => 7, 16 => 8, 17 => array(1,14), 23 => array(1,15)), array( 18 => array(2,17), 22 => array(2,17)), array( 18 => array(2,15), 22 => array(2,15)), array( 22 => array(2,21), 24 => array(2,21)),
    );

    private $defaultActions = array(
        16 => array(2, 6)
    );

    /**
     * @param  string                $input JSON string
     * @return null|ParsingException null if no error is found, a ParsingException containing all details otherwise
     */
    public function lint($input)
    {
        try {
            $this->parse($input);
        } catch (ParsingException $e) {
            return $e;
        }
    }

    /**
     * @param  string           $input JSON string
     * @return mixed
     * @throws ParsingException
     */
    public function parse($input, $flags = 0)
    {
        $this->failOnBOM($input);

        $this->flags = $flags;

        $this->stack = array(0);
        $this->vstack = array(null);
        $this->lstack = array();

        $yytext = '';
        $yylineno = 0;
        $yyleng = 0;
        $recovering = 0;
        $TERROR = 2;
        $EOF = 1;

        $this->lexer = new Lexer();
        $this->lexer->setInput($input);

        $yyloc = $this->lexer->yylloc;
        $this->lstack[] = $yyloc;

        $symbol = null;
        $preErrorSymbol = null;
        $state = null;
        $action = null;
        $a = null;
        $r = null;
        $yyval = new stdClass;
        $p = null;
        $len = null;
        $newState = null;
        $expected = null;
        $errStr = null;

        while (true) {
            // retreive state number from top of stack
            $state = $this->stack[count($this->stack)-1];

            // use default actions if available
            if (isset($this->defaultActions[$state])) {
                $action = $this->defaultActions[$state];
            } else {
                if ($symbol == null) {
                    $symbol = $this->lex();
                }
                // read action for current state and first input
                $action = isset($this->table[$state][$symbol]) ? $this->table[$state][$symbol] : false;
            }

            // handle parse error
            if (!$action || !$action[0]) {
                if (!$recovering) {
                    // Report error
                    $expected = array();
                    foreach ($this->table[$state] as $p => $ignore) {
                        if (isset($this->terminals_[$p]) && $p > 2) {
                            $expected[] = "'" . $this->terminals_[$p] . "'";
                        }
                    }

                    $message = null;
                    if (in_array("'STRING'", $expected) && in_array(substr($this->lexer->match, 0, 1), array('"', "'"))) {
                        $message = "Invalid string";
                        if ("'" === substr($this->lexer->match, 0, 1)) {
                            $message .= ", it appears you used single quotes instead of double quotes";
                        } elseif (preg_match('{".+?(\\\\[^"bfnrt/\\\\u])}', $this->lexer->getUpcomingInput(), $match)) {
                            $message .= ", it appears you have an unescaped backslash at: ".$match[1];
                        } elseif (preg_match('{"(?:[^"]+|\\\\")*$}m', $this->lexer->getUpcomingInput())) {
                            $message .= ", it appears you forgot to terminated the string, or attempted to write a multiline string which is invalid";
                        }
                    }

                    $errStr = 'Parse error on line ' . ($yylineno+1) . ":\n";
                    $errStr .= $this->lexer->showPosition() . "\n";
                    if ($message) {
                        $errStr .= $message;
                    } else {
                        $errStr .= (count($expected) > 1) ? "Expected one of: " : "Expected: ";
                        $errStr .= implode(', ', $expected);
                    }

                    if (',' === substr(trim($this->lexer->getPastInput()), -1)) {
                        $errStr .= " - It appears you have an extra trailing comma";
                    }

                    $this->parseError($errStr, array(
                        'text' => $this->lexer->match,
                        'token' => !empty($this->terminals_[$symbol]) ? $this->terminals_[$symbol] : $symbol,
                        'line' => $this->lexer->yylineno,
                        'loc' => $yyloc,
                        'expected' => $expected,
                    ));
                }

                // just recovered from another error
                if ($recovering == 3) {
                    if ($symbol == $EOF) {
                        throw new ParsingException($errStr ?: 'Parsing halted.');
                    }

                    // discard current lookahead and grab another
                    $yyleng = $this->lexer->yyleng;
                    $yytext = $this->lexer->yytext;
                    $yylineno = $this->lexer->yylineno;
                    $yyloc = $this->lexer->yylloc;
                    $symbol = $this->lex();
                }

                // try to recover from error
                while (true) {
                    // check for error recovery rule in this state
                    if (array_key_exists($TERROR, $this->table[$state])) {
                        break;
                    }
                    if ($state == 0) {
                        throw new ParsingException($errStr ?: 'Parsing halted.');
                    }
                    $this->popStack(1);
                    $state = $this->stack[count($this->stack)-1];
                }

                $preErrorSymbol = $symbol; // save the lookahead token
                $symbol = $TERROR;         // insert generic error symbol as new lookahead
                $state = $this->stack[count($this->stack)-1];
                $action = isset($this->table[$state][$TERROR]) ? $this->table[$state][$TERROR] : false;
                $recovering = 3; // allow 3 real symbols to be shifted before reporting a new error
            }

            // this shouldn't happen, unless resolve defaults are off
            if (is_array($action[0]) && count($action) > 1) {
                throw new ParsingException('Parse Error: multiple actions possible at state: ' . $state . ', token: ' . $symbol);
            }

            switch ($action[0]) {
                case 1: // shift
                    $this->stack[] = $symbol;
                    $this->vstack[] = $this->lexer->yytext;
                    $this->lstack[] = $this->lexer->yylloc;
                    $this->stack[] = $action[1]; // push state
                    $symbol = null;
                    if (!$preErrorSymbol) { // normal execution/no error
                        $yyleng = $this->lexer->yyleng;
                        $yytext = $this->lexer->yytext;
                        $yylineno = $this->lexer->yylineno;
                        $yyloc = $this->lexer->yylloc;
                        if ($recovering > 0) {
                            $recovering--;
                        }
                    } else { // error just occurred, resume old lookahead f/ before error
                        $symbol = $preErrorSymbol;
                        $preErrorSymbol = null;
                    }
                    break;

                case 2: // reduce
                    $len = $this->productions_[$action[1]][1];

                    // perform semantic action
                    $yyval->token = $this->vstack[count($this->vstack) - $len]; // default to $$ = $1
                    // default location, uses first token for firsts, last for lasts
                    $yyval->store = array( // _$ = store
                        'first_line' => $this->lstack[count($this->lstack) - ($len ?: 1)]['first_line'],
                        'last_line' => $this->lstack[count($this->lstack) - 1]['last_line'],
                        'first_column' => $this->lstack[count($this->lstack) - ($len ?: 1)]['first_column'],
                        'last_column' => $this->lstack[count($this->lstack) - 1]['last_column'],
                    );
                    $r = $this->performAction($yyval, $yytext, $yyleng, $yylineno, $action[1], $this->vstack, $this->lstack);

                    if (!$r instanceof Undefined) {
                        return $r;
                    }

                    if ($len) {
                        $this->popStack($len);
                    }

                    $this->stack[] = $this->productions_[$action[1]][0];    // push nonterminal (reduce)
                    $this->vstack[] = $yyval->token;
                    $this->lstack[] = $yyval->store;
                    $newState = $this->table[$this->stack[count($this->stack)-2]][$this->stack[count($this->stack)-1]];
                    $this->stack[] = $newState;
                    break;

                case 3: // accept

                    return true;
            }
        }

        return true;
    }

    protected function parseError($str, $hash)
    {
        throw new ParsingException($str, $hash);
    }

    // $$ = $tokens // needs to be passed by ref?
    // $ = $token
    // _$ removed, useless?
    private function performAction(stdClass $yyval, $yytext, $yyleng, $yylineno, $yystate, &$tokens)
    {
        // $0 = $len
        $len = count($tokens) - 1;
        switch ($yystate) {
        case 1:
            $yytext = preg_replace_callback('{(?:\\\\["bfnrt/\\\\]|\\\\u[a-fA-F0-9]{4})}', array($this, 'stringInterpolation'), $yytext);
            $yyval->token = $yytext;
            break;
        case 2:
            if (strpos($yytext, 'e') !== false || strpos($yytext, 'E') !== false) {
                $yyval->token = floatval($yytext);
            } else {
                $yyval->token = strpos($yytext, '.') === false ? intval($yytext) : floatval($yytext);
            }
            break;
        case 3:
            $yyval->token = null;
            break;
        case 4:
            $yyval->token = true;
            break;
        case 5:
            $yyval->token = false;
            break;
        case 6:
            return $yyval->token = $tokens[$len-1];
        case 13:
            if ($this->flags & self::PARSE_TO_ASSOC) {
                $yyval->token = array();
            } else {
                $yyval->token = new stdClass;
            }
            break;
        case 14:
            $yyval->token = $tokens[$len-1];
            break;
        case 15:
            $yyval->token = array($tokens[$len-2], $tokens[$len]);
            break;
        case 16:
            $property = $tokens[$len][0] === '' ? '_empty_' : $tokens[$len][0];
            if ($this->flags & self::PARSE_TO_ASSOC) {
                $yyval->token = array();
                $yyval->token[$property] = $tokens[$len][1];
            } else {
                $yyval->token = new stdClass;
                $yyval->token->$property = $tokens[$len][1];
            }
            break;
        case 17:
            if ($this->flags & self::PARSE_TO_ASSOC) {
                $yyval->token =& $tokens[$len-2];
                $key = $tokens[$len][0];
                if (($this->flags & self::DETECT_KEY_CONFLICTS) && isset($tokens[$len-2][$key])) {
                    $errStr = 'Parse error on line ' . ($yylineno+1) . ":\n";
                    $errStr .= $this->lexer->showPosition() . "\n";
                    $errStr .= "Duplicate key: ".$tokens[$len][0];
                    throw new DuplicateKeyException($errStr, $tokens[$len][0], array('line' => $yylineno+1));
                } elseif (($this->flags & self::ALLOW_DUPLICATE_KEYS) && isset($tokens[$len-2][$key])) {
                    $duplicateCount = 1;
                    do {
                        $duplicateKey = $key . '.' . $duplicateCount++;
                    } while (isset($tokens[$len-2][$duplicateKey]));
                    $key = $duplicateKey;
                }
                $tokens[$len-2][$key] = $tokens[$len][1];
            } else {
                $yyval->token = $tokens[$len-2];
                $key = $tokens[$len][0] === '' ? '_empty_' : $tokens[$len][0];
                if (($this->flags & self::DETECT_KEY_CONFLICTS) && isset($tokens[$len-2]->{$key})) {
                    $errStr = 'Parse error on line ' . ($yylineno+1) . ":\n";
                    $errStr .= $this->lexer->showPosition() . "\n";
                    $errStr .= "Duplicate key: ".$tokens[$len][0];
                    throw new DuplicateKeyException($errStr, $tokens[$len][0], array('line' => $yylineno+1));
                } elseif (($this->flags & self::ALLOW_DUPLICATE_KEYS) && isset($tokens[$len-2]->{$key})) {
                    $duplicateCount = 1;
                    do {
                        $duplicateKey = $key . '.' . $duplicateCount++;
                    } while (isset($tokens[$len-2]->$duplicateKey));
                    $key = $duplicateKey;
                }
                $tokens[$len-2]->$key = $tokens[$len][1];
            }
            break;
        case 18:
            $yyval->token = array();
            break;
        case 19:
            $yyval->token = $tokens[$len-1];
            break;
        case 20:
            $yyval->token = array($tokens[$len]);
            break;
        case 21:
            $tokens[$len-2][] = $tokens[$len];
            $yyval->token = $tokens[$len-2];
            break;
        }

        return new Undefined();
    }

    private function stringInterpolation($match)
    {
        switch ($match[0]) {
        case '\\\\':
            return '\\';
        case '\"':
            return '"';
        case '\b':
            return chr(8);
        case '\f':
            return chr(12);
        case '\n':
            return "\n";
        case '\r':
            return "\r";
        case '\t':
            return "\t";
        case '\/':
            return "/";
        default:
            return html_entity_decode('&#x'.ltrim(substr($match[0], 2), '0').';', 0, 'UTF-8');
        }
    }

    private function popStack($n)
    {
        $this->stack = array_slice($this->stack, 0, - (2 * $n));
        $this->vstack = array_slice($this->vstack, 0, - $n);
        $this->lstack = array_slice($this->lstack, 0, - $n);
    }

    private function lex()
    {
        $token = $this->lexer->lex() ?: 1; // $end = 1
        // if token isn't its numeric value, convert
        if (!is_numeric($token)) {
            $token = isset($this->symbols[$token]) ? $this->symbols[$token] : $token;
        }

        return $token;
    }

    private function failOnBOM($input)
    {
        // UTF-8 ByteOrderMark sequence
        $bom = "\xEF\xBB\xBF";

        if (substr($input, 0, 3) === $bom) {
            $this->parseError("BOM detected, make sure your input does not include a Unicode Byte-Order-Mark", array());
        }
    }
}
