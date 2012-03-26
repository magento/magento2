<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Inline Translations PHP part
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Translate_Inline
{
    /**
     * Regular Expression for detected and replace translate
     *
     * @var string
     */
    protected $_tokenRegex = '\{\{\{(.*?)\}\}\{\{(.*?)\}\}\{\{(.*?)\}\}\{\{(.*?)\}\}\}';

    /**
     * Response body or JSON content string
     *
     * @var string
     */
    protected $_content;

    /**
     * Is enabled and allowed inline translates flags
     *
     * @var bool
     */
    protected $_isAllowed;

    /**
     * Flag about inserted styles and scripts for inline translates
     *
     * @var bool
     */
    protected $_isScriptInserted    = false;

    /**
     * Current content is JSON or Response body
     *
     * @var bool
     */
    protected $_isJson              = false;

    /**
     * Get max translate block in same tag
     *
     * @var int
     */
    protected $_maxTranslateBlocks    = 7;

    /**
     * List of global tags
     *
     * @var array
     */
    protected $_allowedTagsGlobal = array(
        'script'    => 'String in Javascript',
        'title'     => 'Page title',
    );

    /**
     * List of simple tags
     *
     * @var array
     */
    protected $_allowedTagsSimple = array(
        'legend'        => 'Caption for the fieldset element',
        'label'         => 'Label for an input element.',
        'button'        => 'Push button',
        'a'             => 'Link label',
        'b'             => 'Bold text',
        'strong'        => 'Strong emphasized text',
        'i'             => 'Italic text',
        'em'            => 'Emphasized text',
        'u'             => 'Underlined text',
        'sup'           => 'Superscript text',
        'sub'           => 'Subscript text',
        'span'          => 'Span element',
        'small'         => 'Smaller text',
        'big'           => 'Bigger text',
        'address'       => 'Contact information',
        'blockquote'    => 'Long quotation',
        'q'             => 'Short quotation',
        'cite'          => 'Citation',
        'dt'            => 'Item in a definition list',
        'dd'            => 'Item description in a definition list.',
        'caption'       => 'Table caption',
        'th'            => 'Header cell in a table',
        'abbr'          => 'Abbreviated phrase',
        'acronym'       => 'An acronym',
        'var'           => 'Variable part of a text',
        'dfn'           => 'Term',
        'strike'        => 'Strikethrough text',
        'del'           => 'Deleted text',
        'ins'           => 'Inserted text',
        'h1'            => 'Heading level 1',
        'h2'            => 'Heading level 2',
        'h3'            => 'Heading level 3',
        'h4'            => 'Heading level 4',
        'h5'            => 'Heading level 5',
        'h6'            => 'Heading level 6',
        'p'             => 'Paragraph',
        'pre'           => 'Preformatted text',
        'center'        => 'Centered text',
        'select'        => 'List options',
    );

    /**
     * Is enabled and allowed Inline Translates
     *
     * @param mixed $store
     * @return bool
     */
    public function isAllowed($store = null)
    {
        if (is_null($store)) {
            $store = Mage::app()->getStore();
        }
        if (!$store instanceof Mage_Core_Model_Store) {
            $store = Mage::app()->getStore($store);
        }

        if (is_null($this->_isAllowed)) {
            if (Mage::getDesign()->getArea() == 'adminhtml') {
                $active = Mage::getStoreConfigFlag('dev/translate_inline/active_admin', $store);
            } else {
                $active = Mage::getStoreConfigFlag('dev/translate_inline/active', $store);
            }

            $this->_isAllowed = $active && Mage::helper('Mage_Core_Helper_Data')->isDevAllowed($store);
        }

        $translate = Mage::getSingleton('Mage_Core_Model_Translate');
        /* @var $translate Mage_Core_Model_Translate */

        return $translate->getTranslateInline() && $this->_isAllowed;
    }

    /**
     * Parse and save edited translate
     *
     * @param array $translate
     * @return Mage_Core_Model_Translate_Inline
     */
    public function processAjaxPost($translate)
    {
        if (!$this->isAllowed()) {
            return $this;
        }

        /* @var $resource Mage_Core_Model_Resource_Translate_String */
        $resource = Mage::getResourceModel('Mage_Core_Model_Resource_Translate_String');
        foreach ($translate as $t) {
            if (Mage::getDesign()->getArea() == 'adminhtml') {
                $storeId = 0;
            } else if (empty($t['perstore'])) {
                $resource->deleteTranslate($t['original'], null, false);
                $storeId = 0;
            } else {
                $storeId = Mage::app()->getStore()->getId();
            }

            $resource->saveTranslate($t['original'], $t['custom'], null, $storeId);
        }

        return $this;
    }

    /**
     * Strip inline translations from text
     *
     * @param array|string $body
     * @return Mage_Core_Model_Translate_Inline
     */
    public function stripInlineTranslations(&$body)
    {
        if (is_array($body)) {
            foreach ($body as &$part) {
                $this->stripInlineTranslations($part);
            }
        } else if (is_string($body)) {
            $body = preg_replace('#' . $this->_tokenRegex . '#', '$1', $body);
        }
        return $this;
    }

    /**
     * Replace translate templates to HTML fragments
     *
     * @param array|string $body
     * @return Mage_Core_Model_Translate_Inline
     */
    public function processResponseBody(&$body)
    {
        if (!$this->isAllowed()) {
            if (Mage::getDesign()->getArea() == 'adminhtml') {
                $this->stripInlineTranslations($body);
            }
            return $this;
        }

        if (is_array($body)) {
            foreach ($body as &$part) {
                $this->processResponseBody($part);
            }
        } else if (is_string($body)) {
            $this->_content = $body;

            $this->_specialTags();
            $this->_tagAttributes();
            $this->_otherText();
            $this->_insertInlineScriptsHtml();

            $body = $this->_content;
        }

        return $this;
    }

    /**
     * Add translate js to body
     */
    protected function _insertInlineScriptsHtml()
    {
        if ($this->_isScriptInserted || stripos($this->_content, '</body>')===false) {
            return;
        }

        $url_prefix = Mage::app()->getStore()->isAdmin() ? 'adminhtml' : 'core';
        $ajaxUrl = Mage::getUrl($url_prefix . '/ajax/translate',
            array('_secure'=>Mage::app()->getStore()->isCurrentlySecure()));
        $trigImg = Mage::getDesign()->getSkinUrl('Mage_Core::fam_book_open.png');

        ob_start();
        $design = Mage::getDesign();
?>
<script type="text/javascript" src="<?php echo $design->getSkinUrl('prototype/window.js') ?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $design->getSkinUrl('prototype/windows/themes/default.css') ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo $design->getSkinUrl('Mage_Core::prototype/magento.css') ?>"/>

<script type="text/javascript" src="<?php echo $design->getSkinUrl('mage/translate_inline.js') ?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $design->getSkinUrl('mage/translate_inline.css') ?>"/>

<div id="translate-inline-trig"><img src="<?php echo $trigImg ?>" alt="[TR]"/></div>
<script type="text/javascript">
    new TranslateInline('translate-inline-trig', '<?php echo $ajaxUrl ?>', '<?php
        echo Mage::getDesign()->getArea() ?>');
</script>
<?php
        $html = ob_get_clean();

        $this->_content = str_ireplace('</body>', $html . '</body>', $this->_content);

        $this->_isScriptInserted = true;
    }

    /**
     * Escape Translate data
     *
     * @param string $string
     * @return string
     */
    protected function _escape($string)
    {
        return str_replace("'", "\\'", htmlspecialchars($string));
    }

    /**
     * Get attribute location
     *
     * @param array $matches
     * @param array $options
     * @return string
     */
    protected function _getAttributeLocation($matches, $options)
    {
        return 'Tag attribute (ALT, TITLE, etc.)';
    }

    /**
     * Get tag location
     *
     * @param array $matches
     * @param array $options
     * @return string
     */
    protected function _getTagLocation($matches, $options)
    {
        $tagName = strtolower($options['tagName']);

        if (isset($options['tagList'][$tagName])) {
            return $options['tagList'][$tagName];
        }

        return ucfirst($tagName) . ' Text';
    }

    /**
     * Get translate data by regexp
     *
     * @param string $regexp
     * @param string $text
     * @param string|array $locationCallback
     * @param array $options
     * @return array
     */
    protected function _getTranslateData($regexp, &$text, $locationCallback, $options = array())
    {
        $trArr = array();
        $next = 0;
        while (preg_match($regexp, $text, $m, PREG_OFFSET_CAPTURE, $next)) {
            $trArr[] = json_encode(array(
                'shown' => $m[1][0],
                'translated' => $m[2][0],
                'original' => $m[3][0],
                'location' => call_user_func($locationCallback, $m, $options),
                'scope' => $m[4][0],
            ));
            $text = substr_replace($text, $m[1][0], $m[0][1], strlen($m[0][0]));
            $next = $m[0][1];
        }
        return $trArr;
    }


    /**
     * Prepare tags inline translates
     *
     */
    protected function _tagAttributes()
    {
        if ($this->getIsJson()) {
            $quoteHtml   = '\"';
        } else {
            $quoteHtml   = '"';
        }

        $tagMatch   = array();
        $nextTag    = 0;
        $tagRegExp  = '#<([a-z]+)\s*?[^>]+?((' . $this->_tokenRegex . ')[^>]*?)+/?>#i';
        while (preg_match($tagRegExp, $this->_content, $tagMatch, PREG_OFFSET_CAPTURE, $nextTag)) {
            $next       = 0;
            $tagHtml    = $tagMatch[0][0];
            $m          = array();
            $attrRegExp = '#' . $this->_tokenRegex . '#';
            $trArr = $this->_getTranslateData($attrRegExp, $tagHtml, array($this, '_getAttributeLocation'));
            if ($trArr) {
                $transRegExp = '# translate=' . $quoteHtml . '\[([^'.preg_quote($quoteHtml).']*)]' . $quoteHtml . '#i';
                if (preg_match($transRegExp, $tagHtml, $m)) {
                    $tagHtml = str_replace($m[0], '', $tagHtml); //remove tra
                    $trAttr  = ' translate=' . $quoteHtml
                        . htmlspecialchars('[' . $m[1] . ',' . join(',', $trArr) . ']') . $quoteHtml;
                } else {
                    $trAttr  = ' translate=' . $quoteHtml
                        . htmlspecialchars('[' . join(',', $trArr) . ']') . $quoteHtml;
                }
                $this->_content = substr_replace($this->_content, $tagHtml, $tagMatch[0][1], strlen($tagMatch[0][0]));
            }
            $nextTag = $tagMatch[0][1] + strlen($tagHtml);
        }
    }

    /**
     * Get html quote symbol
     *
     * @return string
     */
    protected function _getHtmlQuote()
    {
        if ($this->getIsJson()) {
            return '\"';
        } else {
            return '"';
        }
    }

    /**
     * Prepare special tags
     */
    protected function _specialTags() {
        $this->_translateTags($this->_content, $this->_allowedTagsGlobal, '_applySpecialTagsFormat', false);
        $this->_translateTags($this->_content, $this->_allowedTagsSimple, '_applySimpleTagsFormat', true);
    }

    /**
     * Format translate for special tags
     *
     * @param string $tagHtml
     * @param string  $tagName
     * @param array $trArr
     * @return string
     */
    protected function _applySpecialTagsFormat($tagHtml, $tagName, $trArr)
    {
        return $tagHtml . '<span class="translate-inline-' . $tagName
            . '" translate='
            . $this->_getHtmlQuote()
            . htmlspecialchars('[' . join(',', $trArr) . ']')
            . $this->_getHtmlQuote() . '>'
            . strtoupper($tagName) . '</span>';
    }

    /**
     * Format translate for simple tags
     *
     * @param string $tagHtml
     * @param string  $tagName
     * @param array $trArr
     * @return string
     */
    protected function _applySimpleTagsFormat($tagHtml, $tagName, $trArr)
    {
        return substr($tagHtml, 0, strlen($tagName) + 1)
            . ' translate='
            . $this->_getHtmlQuote() . htmlspecialchars( '[' . join(',', $trArr) . ']')
            . $this->_getHtmlQuote()
            . substr($tagHtml, strlen($tagName) + 1);
    }

    /**
     * Prepare simple tags
     *
     * @param string $body
     * @param array $tagsList
     * @param string|array $formatCallback
     * @param bool $isNeedTranslateAttributes
     */
    protected function _translateTags(&$body, $tagsList, $formatCallback, $isNeedTranslateAttributes)
    {
        if ($this->getIsJson()) {
            $quoteHtml   = '\"';
        } else {
            $quoteHtml   = '"';
        }

        $nextTag = 0;

        $tags = implode('|', array_keys($tagsList));
        $tagRegExp  = '#<(' . $tags . ')(\s*[^>]*>)#i';

        $tagMatch = array();
        while (preg_match($tagRegExp, $body, $tagMatch, PREG_OFFSET_CAPTURE, $nextTag)) {
            $tagName  = strtolower($tagMatch[1][0]);
            $tagClosurePos = $this->findEndOfTag($body, $tagName, $tagMatch[0][1]);
            if ($tagClosurePos === false) {
                $nextTag += strlen($tagMatch[0][0]);
                continue;
            }

            $tagLength = $tagClosurePos - $tagMatch[0][1];

            $tagStartLength = strlen($tagMatch[0][0]);

            $tagHtml = $tagMatch[0][0] ;
            $tagEnd = substr($body, $tagMatch[0][1] + $tagStartLength, $tagLength - $tagStartLength);

            if ($isNeedTranslateAttributes
                && preg_match_all('#' . $this->_tokenRegex . '#', $tagEnd, $m)
                && count($m[0]) > $this->_maxTranslateBlocks
            ) {
                $this->_translateTags($tagEnd, $tagsList, $formatCallback, $isNeedTranslateAttributes);
            }

            if ($isNeedTranslateAttributes) {
                $this->_tagAttributes($tagEnd);
            }
            $tagHtml .= $tagEnd;

            $trArr = $this->_getTranslateData(
                '#' . $this->_tokenRegex . '#i',
                $tagHtml,
                array($this, '_getTagLocation'),
                array(
                    'tagName' => $tagName,
                    'tagList' => $tagsList
                )
            );

            if (!empty($trArr)) {
                $trArr = array_unique($trArr);
                $tagHtml = call_user_func(array($this, $formatCallback), $tagHtml, $tagName, $trArr);

                $body = substr_replace($body, $tagHtml, $tagMatch[0][1], $tagLength);
            }
            $nextTag = $tagClosurePos;
        }
    }

    /**
     * Find end of tag
     *
     * @param $body
     * @param $tagName
     * @param $from
     * @return bool|int return false if end of tag is not found
     */
    private function findEndOfTag($body, $tagName, $from)
    {
        $openTag = '<' . $tagName;
        $closeTag = '</' . $tagName;
        $end = $from + strlen($openTag);
        $length = $end - $from;
        while (substr_count($body, $openTag, $from, $length) != substr_count($body, $closeTag, $from, $length)) {
            $end = strpos($body, $closeTag, $end + strlen($closeTag) - 1);
            if ($end === false) {
                return false;
            }
            $length = $end - $from  + strlen($closeTag);
        }
        if (preg_match('#<\/' . $tagName .'\s*?>#i', $body, $tagMatch, null, $end)) {
            return $end + strlen($tagMatch[0]);
        } else {
            return false;
        }
    }

    /**
     * Prepare other text inline translates
     */
    protected function _otherText()
    {
        if ($this->getIsJson()) {
            $quoteHtml = '\"';
        } else {
            $quoteHtml = '"';
        }

        $next = 0;
        $m    = array();
        while (preg_match('#' . $this->_tokenRegex . '#', $this->_content, $m, PREG_OFFSET_CAPTURE, $next)) {
            $tr = json_encode(array(
                'shown' => $m[1][0],
                'translated' => $m[2][0],
                'original' => $m[3][0],
                'location' => 'Text',
                'scope' => $m[4][0],
            ));

            $spanHtml = '<span translate=' . $quoteHtml . htmlspecialchars('[' . $tr . ']') . $quoteHtml
                . '>' . $m[1][0] . '</span>';
            $this->_content = substr_replace($this->_content, $spanHtml, $m[0][1], strlen($m[0][0]));
            $next = $m[0][1] + strlen($spanHtml) - 1;
        }

    }

    /**
     * Retrieve flag about parsed content is Json
     *
     * @return bool
     */
    public function getIsJson()
    {
        return $this->_isJson;
    }

    /**
     * Set flag about parsed content is Json
     *
     * @param bool $flag
     * @return Mage_Core_Model_Translate_Inline
     */
    public function setIsJson($flag)
    {
        $this->_isJson = (bool)$flag;
        return $this;
    }
}
