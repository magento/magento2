<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Markup
 * @subpackage Parser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Textile.php 20277 2010-01-14 14:17:12Z kokx $
 */

/**
 * @see Zend_Markup_TokenList
 */
#require_once 'Zend/Markup/TokenList.php';

/**
 * @see Zend_Markup_Parser_ParserInterface
 */
#require_once 'Zend/Markup/Parser/ParserInterface.php';

/**
 * @category   Zend
 * @package    Zend_Markup
 * @subpackage Parser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Markup_Parser_Textile implements Zend_Markup_Parser_ParserInterface
{

    const STATE_SCAN          = 0;
    const STATE_NEW_PARAGRAPH = 1;
    const STATE_NEWLINE       = 2;

    const MATCH_ATTR_CLASSID = '\((?<attr_class>[a-zA-Z0-9_]+)?(?:\#(?<attr_id>[a-zA-Z0-9_]+))?\)';
    const MATCH_ATTR_STYLE   = "\{(?<attr_style>[^\}\n]+)\}";
    const MATCH_ATTR_LANG    = '\[(?<attr_lang>[a-zA-Z_]+)\]';
    const MATCH_ATTR_ALIGN   = '(?<attr_align>\<\>?|\>|=)';



    /**
     * Token tree
     *
     * @var Zend_Markup_TokenList
     */
    protected $_tree;

    /**
     * Current token
     *
     * @var Zend_Markup_Token
     */
    protected $_current;

    /**
     * Source to tokenize
     *
     * @var string
     */
    protected $_value = '';

    /**
     * Length of the value
     *
     * @var int
     */
    protected $_valueLen = 0;

    /**
     * Current pointer
     *
     * @var int
     */
    protected $_pointer = 0;

    /**
     * The buffer
     *
     * @var string
     */
    protected $_buffer = '';

    /**
     * Simple tag translation
     *
     * @var array
     */
    protected $_simpleTags = array(
        '*'  => 'strong',
        '**' => 'bold',
        '_'  => 'emphasized',
        '__' => 'italic',
        '??' => 'citation',
        '-'  => 'deleted',
        '+'  => 'insert',
        '^'  => 'superscript',
        '~'  => 'subscript',
        '%'  => 'span',
        // these are a little more complicated
        '@'  => 'code',
        '!'  => 'img',
    );

    /**
     * Token array
     *
     * @var array
     */
    protected $_tokens = array();


    /**
     * Prepare the parsing of a Textile string, the real parsing is done in {@link _parse()}
     *
     * @param string $value
     *
     * @return array
     */
    public function parse($value)
    {
        if (!is_string($value)) {
            /**
             * @see Zend_Markup_Parser_Exception
             */
            #require_once 'Zend/Markup/Parser/Exception.php';
            throw new Zend_Markup_Parser_Exception('Value to parse should be a string.');
        }
        if (empty($value)) {
            /**
             * @see Zend_Markup_Parser_Exception
             */
            #require_once 'Zend/Markup/Parser/Exception.php';
            throw new Zend_Markup_Parser_Exception('Value to parse cannot be left empty.');
        }

        // first make we only have LF newlines, also trim the value
        $this->_value = str_replace(array("\r\n", "\r"), "\n", $value);
        $this->_value = trim($this->_value);

        // initialize variables and tokenize
        $this->_valueLen = iconv_strlen($this->_value, 'UTF-8');
        $this->_pointer  = 0;
        $this->_buffer   = '';
        $this->_temp     = array();
        $this->_tokens   = array();

        $this->_tokenize();

        // create the tree
        $this->_tree     = new Zend_Markup_TokenList();

        $this->_current  = new Zend_Markup_Token('', Zend_Markup_Token::TYPE_NONE, 'Zend_Markup_Root');
        $this->_tree->addChild($this->_current);

        $this->_createTree();

        return $this->_tree;
    }

    /**
     * Tokenize a textile string
     *
     * @return array
     */
    protected function _tokenize()
    {
        $state    = self::STATE_NEW_PARAGRAPH;

        $attrsMatch = implode('|', array(
            self::MATCH_ATTR_CLASSID,
            self::MATCH_ATTR_STYLE,
            self::MATCH_ATTR_LANG,
            self::MATCH_ATTR_ALIGN
        ));

        $paragraph = '';

        while ($this->_pointer < $this->_valueLen) {
            switch ($state) {
                case self::STATE_SCAN:
                    $matches = array(); //[^\n*_?+~%@!-]
                    $acronym = '(?<acronym>[A-Z]{2,})\((?<title>[^\)]+)\)';
                    $regex   = '#\G(?<text>.*?)(?:'
                             . "(?:(?<nl_paragraph>\n{2,})|(?<nl_break>\n))|"
                             . '(?<tag>'
                             . "(?<name>\*{1,2}|_{1,2}|\?{2}|\-|\+|\~|\^|%|@|!|$|{$acronym}"
                             . '|":(?<url>[^\s]+)|")'
                             . "(?:{$attrsMatch})*)"
                             . ')#si';
                    preg_match($regex, $this->_value, $matches, null, $this->_pointer);

                    $this->_pointer += strlen($matches[0]);

                    if (!empty($matches['text'])) {
                        $this->_buffer .= $matches['text'];
                    }

                    // first add the buffer
                    if (!empty($this->_buffer)) {
                        $this->_tokens[] = array(
                            'tag'  => $this->_buffer,
                            'type' => Zend_Markup_Token::TYPE_NONE
                        );
                        $this->_buffer = '';
                    }

                    if (!empty($matches['nl_paragraph'])) {
                        $this->_temp = array(
                            'tag'        => $matches['nl_paragraph'],
                            'name'       => 'p',
                            'type'       => Zend_Markup_Token::TYPE_TAG,
                            'attributes' => array()
                        );

                        $state = self::STATE_NEW_PARAGRAPH;
                    } elseif (!empty($matches['nl_break'])) {
                        $this->_tokens[] = array(
                            'tag'        => $matches['nl_break'],
                            'name'       => 'break',
                            'type'       => Zend_Markup_Token::TYPE_TAG,
                            'attributes' => array()
                        );

                        $state   = self::STATE_NEWLINE;
                    } elseif (!empty($matches['tag'])) {
                        if (isset($this->_simpleTags[$matches['name']])) {
                            // now add the new token
                            $this->_tokens[] = array(
                                'tag'        => $matches['tag'],
                                'type'       => Zend_Markup_Token::TYPE_TAG,
                                'name'       => $this->_simpleTags[$matches['name']],
                                'attributes' => $this->_extractAttributes($matches)
                            );
                        } else {
                            $attributes = $this->_extractAttributes($matches);
                            if ($matches['tag'][0] == '"') {
                                $name = 'url';
                                if (isset($matches['url'])) {
                                    $attributes['url'] = $matches['url'];
                                }
                                $this->_tokens[] = array(
                                    'tag'        => $matches['tag'],
                                    'type'       => Zend_Markup_Token::TYPE_TAG,
                                    'name'       => $name,
                                    'attributes' => $attributes
                                );
                            } else {
                                $name = 'acronym';
                                $this->_tokens[] = array(
                                    'tag'        => '',
                                    'type'       => Zend_Markup_Token::TYPE_TAG,
                                    'name'       => 'acronym',
                                    'attributes' => array(
                                        'title' => $matches['title']
                                    )
                                );
                                $this->_tokens[] = array(
                                    'tag'  => $matches['acronym'],
                                    'type' => Zend_Markup_Token::TYPE_NONE
                                );
                                $this->_tokens[] = array(
                                    'tag'        => '(' . $matches['title'] . ')',
                                    'type'       => Zend_Markup_Token::TYPE_TAG,
                                    'name'       => 'acronym',
                                    'attributes' => array()
                                );
                            }
                        }
                        $state = self::STATE_SCAN;
                    }

                    break;
                case self::STATE_NEW_PARAGRAPH:
                    if (empty($this->_temp)) {
                        $this->_temp = array(
                            'tag'        => '',
                            'name'       => 'p',
                            'type'       => Zend_Markup_token::TYPE_TAG,
                            'attributes' => array()
                        );
                    } else {
                        $this->_tokens[] = array(
                            'tag'        => "\n",
                            'name'       => 'p',
                            'type'       => Zend_Markup_Token::TYPE_TAG,
                            'attributes' => array()
                        );
                        $this->_temp['tag'] = substr($this->_temp['tag'], 1);
                    }

                    $matches = array(); //[^\n*_?+~%@!-] (\()? [^()]+ (?(1)\))
                    $regex   = "#\G(?<name>(h[1-6]|p)|(?:\#|\*))(?:{$attrsMatch})*(?(2)\.\s|\s)#i";
                    if (!preg_match($regex, $this->_value, $matches, null, $this->_pointer)) {
                        $this->_tokens[] = $this->_temp;
                        $state    = self::STATE_SCAN;
                        break;
                    }

                    $this->_pointer += strlen($matches[0]);

                    if ($matches['name'] == 'p') {
                        $this->_temp['tag']       .= $matches[0];
                        $this->_temp['attributes'] = $this->_extractAttributes($matches);

                        $this->_tokens[]    = $this->_temp;
                        $this->_temp = array();
                    } else {
                        $this->_tokens[] = $this->_temp;
                        $this->_temp = array();

                        $name       = $matches['name'];
                        $attributes = $this->_extractAttributes($matches);

                        if ($name == '#') {
                            $name               = 'list';
                            $attributes['list'] = 'decimal';
                        } elseif ($name == '*') {
                            $name = 'list';
                        }

                        $this->_tokens[] = array(
                            'tag'        => $matches[0],
                            'name'       => $name,
                            'type'       => Zend_Markup_Token::TYPE_TAG,
                            'attributes' => $attributes
                        );
                    }

                    $state = self::STATE_SCAN;
                    break;
                case self::STATE_NEWLINE:
                    $matches = array(); //[^\n*_?+~%@!-]
                    $regex   = "#\G(?<name>(h[1-6])|(?:\#|\*))(?:{$attrsMatch})*(?(2)\.\s|\s)#si";
                    if (!preg_match($regex, $this->_value, $matches, null, $this->_pointer)) {
                        $state = self::STATE_SCAN;
                        break;
                    }

                    $this->_pointer += strlen($matches[0]);

                    $name       = $matches['name'];
                    $attributes = $this->_extractAttributes($matches);

                    if ($name == '#') {
                        $name               = 'list';
                        $attributes['list'] = 'decimal';
                    } elseif ($name == '*') {
                        $name = 'list';
                    }

                    $this->_tokens[] = array(
                        'tag'        => $matches[0],
                        'name'       => $name,
                        'type'       => Zend_Markup_Token::TYPE_TAG,
                        'attributes' => $attributes
                    );
                    break;
            }
        }
    }

    /**
     * Create a tree from the tokenized text
     *
     * @return void
     */
    protected function _createTree()
    {
        $inside = true;

        foreach ($this->_tokens as $key => $token) {
            // first check if the token is a stopper
            if ($this->_isStopper($token, $this->_current)) {
                if ($this->_current->getName() == 'li') {
                    // list items are handled differently
                    if (isset($this->_tokens[$key + 1])
                        && ($this->_tokens[$key + 1]['type'] == Zend_Markup_Token::TYPE_TAG)
                        && ($this->_tokens[$key + 1]['name'] == 'list')
                    ) {
                        // the next item is a correct tag
                        $this->_current->setStopper($token['tag']);

                        $this->_current = $this->_current->getParent();
                    } else {
                        // close the list
                        $this->_current->setStopper($token['tag']);

                        $this->_current = $this->_current->getParent()->getParent();

                        // go up in the tree until we found the end
                        while ($this->_isStopper($token, $this->_current)) {
                            $this->_current->setStopper($token['tag']);

                            $this->_current = $this->_current->getParent();
                        }
                    }
                } else {
                    // go up in the tree until we found the end of stoppers
                    while ($this->_isStopper($token, $this->_current)) {
                        $this->_current->setStopper($token['tag']);

                        if (!empty($token['attributes'])) {
                            foreach ($token['attributes'] as $name => $value) {
                                $this->_current->addAttribute($name, $value);
                            }
                        }

                        $this->_current = $this->_current->getParent();
                    }
                }
                $inside = true;
            } elseif (($token['type'] == Zend_Markup_Token::TYPE_TAG) && $inside) {
                if ($token['name'] == 'break') {
                    // add the newline and continue parsing
                    $this->_current->addChild(new Zend_Markup_Token(
                        $token['tag'],
                        Zend_Markup_Token::TYPE_NONE,
                        '',
                        array(),
                        $this->_current
                    ));
                } else {
                    // handle a list item
                    if ($token['name'] == 'list') {
                        $attributes = array();
                        if (isset($token['attributes']['list'])) {
                            $attributes['list'] = $token['attributes']['list'];
                            unset($token['attributes']['list']);
                        }

                        if ($this->_current->getName() != 'list') {
                            // the list isn't started yet, create it
                            $child = new Zend_Markup_Token(
                                '',
                                Zend_Markup_Token::TYPE_TAG,
                                'list',
                                $attributes,
                                $this->_current
                            );

                            $this->_current->addChild($child);

                            $this->_current = $child;
                        }
                        $token['name'] = 'li';
                    } elseif (($token['name'] == 'img') || ($token['name'] == 'url')) {
                        $inside = false;
                    }

                    // add the token
                    $child = new Zend_Markup_Token(
                        $token['tag'],
                        Zend_Markup_Token::TYPE_TAG,
                        $token['name'],
                        $token['attributes'],
                        $this->_current
                    );

                    $this->_current->addChild($child);

                    $this->_current = $child;
                }
            } else {
                // simply add the token as text
                $this->_current->addChild(new Zend_Markup_Token(
                    $token['tag'],
                    Zend_Markup_Token::TYPE_NONE,
                    '',
                    array(),
                    $this->_current
                ));
            }
        }
    }

    /**
     * Check if a tag is a stopper
     *
     * @param array $token
     * @param Zend_Markup_Token $current
     *
     * @return bool
     */
    protected function _isStopper(array $token, Zend_Markup_Token $current)
    {
        switch ($current->getName()) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
            case 'list':
            case 'li':
                if (($token['type'] == Zend_Markup_Token::TYPE_TAG)
                    && (($token['name'] == 'break') || ($token['name'] == 'p'))
                ) {
                    return true;
                }
                break;
            case 'break':
                return false;
                break;
            default:
                if (($token['type'] == Zend_Markup_Token::TYPE_TAG) && ($token['name'] == $current->getName())) {
                    return true;
                }
                break;
        }
        return false;
    }

    /**
     * Extract the attributes
     *
     * @param array $matches
     *
     * @return array
     */
    protected function _extractAttributes(array $matches)
    {
        $attributes = array();

        if (!empty($matches['attr_class'])) {
            $attributes['class'] = $matches['attr_class'];
        }
        if (!empty($matches['attr_id'])) {
            $attributes['id'] = $matches['attr_id'];
        }
        if (!empty($matches['attr_style'])) {
            $attributes['style'] = $matches['attr_style'];
        }
        if (!empty($matches['attr_lang'])) {
            $attributes['lang'] = $matches['attr_lang'];
        }
        if (!empty($matches['attr_align'])) {
            switch ($matches['attr_align']) {
                case '=':
                    $attributes['align'] = 'center';
                    break;
                case '>':
                    $attributes['align'] = 'right';
                    break;
                case '<>':
                    $attributes['align'] = 'justify';
                    break;
                default:
                case '<':
                    $attributes['align'] = 'left';
                    break;
            }
        }

        return $attributes;
    }

}