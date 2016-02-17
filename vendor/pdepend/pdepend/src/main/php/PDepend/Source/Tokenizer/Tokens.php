<?php
/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2015, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PDepend\Source\Tokenizer;

/**
 * This interface holds the different tokenizer, builder and parser constants.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
interface Tokens
{
    /**
     * Marks a class token.
     */
    const T_CLASS = 1;

    /**
     * Marks an interface token.
     */
    const T_INTERFACE = 2;

    /**
     * Marks an abstract token.
     */
    const T_ABSTRACT = 3;

    /**
     * Marks a curly brace open.
     */
    const T_CURLY_BRACE_OPEN = 4;

    /**
     * Marks a curly brace close.
     */
    const T_CURLY_BRACE_CLOSE = 5;

    /**
     * Marks a parenthesis open.
     */
    const T_PARENTHESIS_OPEN = 6;

    /**
     * Marks a parenthesis close.
     */
    const T_PARENTHESIS_CLOSE = 7;

    /**
     * Marks a new token.
     */
    const T_NEW = 8;

    /**
     * Marks a function.
     */
    const T_FUNCTION = 9;

    /**
     * Marks a double colon.
     */
    const T_DOUBLE_COLON = 10;

    /**
     * Marks a string token.
     */
    const T_STRING = 11;

    /**
     * Marks a doc comment.
     */
    const T_DOC_COMMENT = 12;

    /**
     * Marks a semicolon.
     */
    const T_SEMICOLON = 13;

    /**
     * Marks a null token.
     */
    const T_NULL = 14;

    /**
     * Marks a true token.
     */
    const T_TRUE = 15;

    /**
     * Marks a false token.
     */
    const T_FALSE = 16;

    /**
     * Marks a array token.
     */
    const T_ARRAY = 17;

    /**
     * Marks a 'parent' token.
     */
    const T_PARENT = 18;

    /**
     * Marks a '=' token.
     */
    const T_EQUAL = 19;

    /**
     * Marks a '&=' token.
     */
    const T_AND_EQUAL = 20;

    /**
     * Marks a '.=' token.
     */
    const T_CONCAT_EQUAL = 21;

    /**
     * Marks a '/=' token.
     */
    const T_DIV_EQUAL = 22;

    /**
     * Marks a '==' token.
     */
    const T_IS_EQUAL = 23;

    /**
     * Marks a '>=' token.
     */
    const T_IS_GREATER_OR_EQUAL = 24;

    /**
     * Marks a '===' token.
     */
    const T_IS_IDENTICAL = 25;

    /**
     * Marks a '!=' or '<>' token.
     */
    const T_IS_NOT_EQUAL = 26;

    /**
     * Marks a '!==' token.
     */
    const T_IS_NOT_IDENTICAL = 27;

    /**
     * Marks a '<=' token.
     */
    const T_IS_SMALLER_OR_EQUAL = 28;

    /**
     * Marks a '-=' token.
     */
    const T_MINUS_EQUAL = 29;

    /**
     * Marks a '%=' token.
     */
    const T_MOD_EQUAL = 30;

    /**
     * Marks a '*=' token.
     */
    const T_MUL_EQUAL = 31;

    /**
     * Marks a '|=' token.
     */
    const T_OR_EQUAL = 32;

    /**
     * Marks a '+=' token.
     */
    const T_PLUS_EQUAL = 33;

    /**
     * Marks a '^=' token.
     */
    const T_XOR_EQUAL = 34;

    /**
     * Marks a '.' token.
     */
    const T_CONCAT = 35;

    /**
     * Marks a 'as' token.
     */
    const T_AS = 36;

    /**
     * Marks a '(array)' cast token.
     */
    const T_ARRAY_CAST = 37;

    /**
     * Marks a '&&' token.
     */
    const T_BOOLEAN_AND = 38;

    /**
     * Marks a '||' token.
     */
    const T_BOOLEAN_OR = 39;

    /**
     * Marks a '(bool)' or '(boolean)' cast token.
     */
    const T_BOOL_CAST = 40;

    /**
     * Marks a 'break' token.
     */
    const T_BREAK = 41;

    /**
     * Marks a 'case' token.
     */
    const T_CASE = 42;

    /**
     * Marks a 'catch' token.
     */
    const T_CATCH = 43;

    /**
     * Marks a '__CLASS__' token.
     */
    const T_CLASS_C = 44;

    /**
     * Marks a 'clone' token.
     */
    const T_CLONE = 45;

    /**
     * Marks a '?>' token.
     */
    const T_CLOSE_TAG = 46;

    /**
     * Marks a 'const' token.
     */
    const T_CONST = 47;

    /**
     * Marks a constant string like 'foo' or "foo".
     */
    const T_CONSTANT_ENCAPSED_STRING = 48;

    /**
     * Marks a 'continue' token.
     */
    const T_CONTINUE = 49;

    /**
     * Marks a '--' token.
     */
    const T_DEC = 50;

    /**
     * Marks a 'declare' token.
     */
    const T_DECLARE = 51;

    /**
     * Marks a 'default' token.
     */
    const T_DEFAULT = 52;

    /**
     * Marks a floating point number.
     */
    const T_DNUMBER = 53;

    /**
     * Marks a 'do' token.
     */
    const T_DO = 54;

    /**
     * Marks a '=>' token.
     */
    const T_DOUBLE_ARROW = 55;

    /**
     * Marks a '(real)', '(float)' or '(double)' cast token.
     */
    const T_DOUBLE_CAST = 56;

    /**
     * Marks a 'echo' token.
     */
    const T_ECHO = 57;

    /**
     * Marks a 'else' token.
     */
    const T_ELSE = 58;

    /**
     * Marks a 'elseif' token.
     */
    const T_ELSEIF = 59;

    /**
     * Marks a 'empty' token.
     */
    const T_EMPTY = 60;

    /**
     * Marks the end of a heredoc block.
     */
    const T_END_HEREDOC = 61;

    /**
     * Marks a 'evil' token.
     */
    const T_EVAL = 62;

    /**
     * Marks a 'exit' or 'die' token.
     */
    const T_EXIT = 63;

    /**
     * Marks a 'extends' token.
     */
    const T_EXTENDS = 64;

    /**
     * Marks a '__FILE__' token.
     */
    const T_FILE = 65;

    /**
     * Marks a 'final' token.
     */
    const T_FINAL = 66;

    /**
     * Marks a 'for' token.
     */
    const T_FOR = 67;

    /**
     * Marks a 'foreach' token.
     */
    const T_FOREACH = 68;

    /**
     * Marks a '__FUNCTION__' token.
     */
    const T_FUNC_C = 69;

    /**
     * Marks a 'global' token.
     */
    const T_GLOBAL = 70;

    /**
     * Marks a '__halt_compiler()' token.
     */
    const T_HALT_COMPILER = 71;

    /**
     * Marks a 'if' token.
     */
    const T_IF = 72;

    /**
     * Marks a 'implements' token.
     */
    const T_IMPLEMENTS = 73;

    /**
     * Marks a '++' token.
     */
    const T_INC = 74;

    /**
     * Marks a 'include' token.
     */
    const T_INCLUDE = 75;

    /**
     * Marks a 'include_once' token.
     */
    const T_INCLUDE_ONCE = 76;

    /**
     * Marks inline html???
     */
    const T_INLINE_HTML = 77;

    /**
     * Marks a 'instanceof' token.
     */
    const T_INSTANCEOF = 78;

    /**
     * Marks a '(int)' or '(integer)' cast token.
     */
    const T_INT_CAST = 79;

    /**
     * Marks a 'isset' token.
     */
    const T_ISSET = 80;

    /**
     * Marks a '__LINE__' token.
     */
    const T_LINE = 81;

    /**
     * Marks a 'list' token.
     */
    const T_LIST = 82;

    /**
     * Marks a integer number token.
     */
    const T_LNUMBER = 83;

    /**
     * Marks a 'and' token.
     */
    const T_LOGICAL_AND = 84;

    /**
     * Marks a 'or' token.
     */
    const T_LOGICAL_OR = 85;

    /**
     * Marks a 'xor' token.
     */
    const T_LOGICAL_XOR = 86;

    /**
     * Marks a '__METHOD__' token.
     */
    const T_METHOD_C = 87;

    /**
     * Marks a '__NAMESPACE__' token.
     */
    const T_NS_C = 88;

    /**
     * A number string token???
     */
    const T_NUM_STRING = 89;

    /**
     * Marks a '(object)' cast token.
     */
    const T_OBJECT_CAST = 90;

    /**
     * Marks a '->' object access token.
     */
    const T_OBJECT_OPERATOR = 91;

    /**
     * Marks a php open token.
     */
    const T_OPEN_TAG = 92;

    /**
     * Marks a php open token.
     */
    const T_OPEN_TAG_WITH_ECHO = 93;

    /**
     * Marks a 'print' token.
     */
    const T_PRINT = 94;

    /**
     * Marks a 'private' token.
     */
    const T_PRIVATE = 95;

    /**
     * Marks a 'public' token.
     */
    const T_PUBLIC = 96;

    /**
     * Marks a 'protected' token.
     */
    const T_PROTECTED = 97;

    /**
     * Marks a 'require' token.
     */
    const T_REQUIRE = 98;

    /**
     * Marks a 'require_once' token.
     */
    const T_REQUIRE_ONCE = 99;

    /**
     * Marks a 'return' token.
     */
    const T_RETURN = 100;

    /**
     * Marks a '<<' token.
     */
    const T_SL = 101;

    /**
     * Marks a '<<=' token.
     */
    const T_SL_EQUAL = 102;

    /**
     * Marks a '>>' token.
     */
    const T_SR = 103;

    /**
     * Marks a '>>=' token.
     */
    const T_SR_EQUAL = 104;

    /**
     * Marks the beginning of a here doc block.
     */
    const T_START_HEREDOC = 105;

    /**
     * Marks a 'static' token.
     */
    const T_STATIC = 106;

    /**
     * Marks a '(string)' cast token.
     */
    const T_STRING_CAST = 107;

    /**
     * Marks a string var name???
     */
    const T_STRING_VARNAME = 108;

    /**
     * Marks a 'switch' token.
     */
    const T_SWITCH = 109;

    /**
     * Marks a 'throw' token.
     */
    const T_THROW = 110;

    /**
     * Marks a 'try' token.
     */
    const T_TRY = 111;

    /**
     * Marks a 'unset' token.
     */
    const T_UNSET = 112;

    /**
     * Marks a '(unset)' cast token.
     */
    const T_UNSET_CAST = 113;

    /**
     * Marks a 'use' token.
     */
    const T_USE = 114;

    /**
     * Marks a 'var' token.
     */
    const T_VAR = 115;

    /**
     * Marks a variable token.
     */
    const T_VARIABLE = 116;

    /**
     * Marks a 'while' token.
     */
    const T_WHILE = 117;

    /**
     * Marks a ',' token.
     */
    const T_COMMA = 118;

    /**
     * Marks a '*' token.
     */
    const T_MUL = 119;

    /**
     * Marks a '[' token.
     */
    const T_SQUARED_BRACKET_OPEN = 120;

    /**
     * Marks a ']' token.
     */
    const T_SQUARED_BRACKET_CLOSE = 121;

    /**
     * Marks a '<' token.
     */
    const T_ANGLE_BRACKET_OPEN = 122;

    /**
     * Marks a '>' token.
     */
    const T_ANGLE_BRACKET_CLOSE = 123;

    /**
     * Marks a '"' token.
     */
    const T_DOUBLE_QUOTE = 124;

    /**
     * Marks a ':' token.
     */
    const T_COLON = 125;

    /**
     * Marks a '@' token.
     */
    const T_AT = 126;

    /**
     * Marks a '+' token.
     */
    const T_PLUS = 127;

    /**
     * Marks a '-' token.
     */
    const T_MINUS = 128;

    /**
     * Marks a '!' token.
     */
    const T_EXCLAMATION_MARK = 129;

    /**
     * Marks a '?' token.
     */
    const T_QUESTION_MARK = 130;

    /**
     * Marks a '&' token.
     */
    const T_BITWISE_AND = 131;

    /**
     * Marks a '|' token.
     */
    const T_BITWISE_OR = 132;

    /**
     * Marks a '~' token.
     */
    const T_BITWISE_NOT = 133;

    /**
     * Marks a '^' token.
     */
    const T_BITWISE_XOR = 134;

    /**
     * Marks a '/' token.
     */
    const T_DIV = 135;

    /**
     * Marks a '%' token.
     */
    const T_MOD = 136;

    /**
     * Marks a comment token.
     */
    const T_COMMENT = 137;

    /**
     * Marks a 'namespace' token.
     */
    const T_NAMESPACE = 138;

    /**
     * Marks an escape token.
     */
    const T_ENCAPSED_AND_WHITESPACE = 139;

    /**
     * Marks a '$' string token.
     */
    const T_DOLLAR = 140;

    /**
     * Marks any character token.
     */
    const T_CHARACTER = 141;

    /**
     * Marks any bad character token.
     */
    const T_BAD_CHARACTER = 142;

    /**
     * Marks a 'self' token.
     */
    const T_SELF = 143;

    /**
     * Marks a '`' backtick token.
     */
    const T_BACKTICK = 144;

    /**
     * Marks a '\' backslash token.
     */
    const T_BACKSLASH = 145;

    /**
     * Marks a '__DIR__' token.
     */
    const T_DIR = 146;

    /**
     * Marks a 'goto' token.
     */
    const T_GOTO = 147;

    /**
     * Alternative end token for an if-statement.
     */
    const T_ENDIF = 148;

    /**
     * Alternative end token for a for-statement.
     */
    const T_ENDFOR = 149;

    /**
     * Alternative end token for a foreach-statement.
     */
    const T_ENDFOREACH = 150;

    /**
     * Alternative end token for a switch-statement.
     */
    const T_ENDSWITCH = 151;

    /**
     * Alternative end token for a while-statement.
     */
    const T_ENDWHILE = 152;

    /**
     * Alternative end token for a declare-statement.
     */
    const T_ENDDECLARE = 153;

    /**
     * Marks a 'trait' keyword token.
     */
    const T_TRAIT = 154;

    /**
     * Marks a '__TRAIT__' magic constant token.
     */
    const T_TRAIT_C = 155;

    /**
     * Token that represents the new Callable type hint.
     */
    const T_CALLABLE = 156;

    /**
     * Token that represents the new 'insteadof' keyword.
     */
    const T_INSTEADOF = 157;

    /**
     * Token that represents the stdClass::class and $class::class constant.
     */
    const T_CLASS_FQN = 158;

    /**
     * Token that represents the new 'yield' keyword.
     */
    const T_YIELD = 159;

    /**
     * Token that represents the 'finally' keyword.
     */
    const T_FINALLY = 160;

    /**
     * Token that represents the '...' token
     *
     * @since 2.0.7
     */
    const T_ELLIPSIS = 161;

    /**
     * Token that represents the '<=>' spaceship operator
     */
    const T_SPACESHIP = 162;

    /**
     * Marks any content not between php tags.
     */
    const T_NO_PHP = 255;
}
