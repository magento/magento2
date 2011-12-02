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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
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
        'option'        => 'Drop-down list option',
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
        'td'            => 'Standard cell in a table',
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
        'center'        => 'Centered text'
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
            $body = preg_replace('#'.$this->_tokenRegex.'#', '$1', $body);
            $body = preg_replace('/{{escape.*?}}/', '', $body);
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

            $this->_tagAttributes();
            $this->_specialTags();
            $this->_otherText();
            $this->_insertInlineScriptsHtml();
            $this->_escapeInline();
            $body = $this->_content;
        }

        return $this;
    }

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
<link rel="stylesheet" type="text/css" href="<?php echo $design->getSkinUrl('prototype/windows/themes/magento.css') ?>"/>

<script type="text/javascript" src="<?php echo $design->getSkinUrl('mage/translate_inline.js') ?>"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $design->getSkinUrl('mage/translate_inline.css') ?>"/>

<div id="translate-inline-trig"><img src="<?php echo $trigImg ?>" alt="[TR]"/></div>
<script type="text/javascript">
    new TranslateInline('translate-inline-trig', '<?php echo $ajaxUrl ?>', '<?php
        echo Mage::getDesign()->getArea() ?>');
</script>
<?php
        $html = ob_get_clean();

        $this->_content = str_ireplace('</body>', $html.'</body>', $this->_content);

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
     * Escapes quoting inside inline translations. Useful when inline translation is inserted inside a JS string.
     * @see Mage_Core_Helper_Translate::inlineTranslateStartMarker()
     * @see Mage_Core_Helper_Translate::inlineTranslateEndMarker()
     * @return Mage_Core_Model_Translate_Inline
     */
    protected function _escapeInline()
    {
        // {{escape='}}some_javascript_with_'_inside{{escape}}
        while (preg_match('/\{\{escape=(.)\}\}(.*?)\{\{escape\}\}/', $this->_content, $matches)) {
            // escape double quote character to make it possible to use it inside ""
            $charToEscape = str_replace('"', '\\"', $matches[1]);
            // preg_replace() used to avoid escaping already escaped quotes
            $part = preg_replace("/[^\\\\]{$charToEscape}/", "\\{$charToEscape}", $matches[2]);
            // Replace markers+string with the string itself
            $this->_content = str_replace($matches[0], $part, $this->_content);
        }
        return $this;
    }

    /**
     * Prepare tags inline translates
     *
     */
    protected function _tagAttributes()
    {
        if ($this->getIsJson()) {
            $quotePatern = '\\\\"';
            $quoteHtml   = '\"';
            $tagEndRegexp = '(\\\\/)?' . '>$';
        } else {
            $quotePatern = '"';
            $quoteHtml   = '"';
            $tagEndRegexp = '/?>$';
        }

        $tagMatch   = array();
        $nextTag    = 0;
        $tagRegExp  = '#<([a-z]+)\s*?[^>]+?((' . $this->_tokenRegex . ')[^/>]*?)+(/?(>))#i';
        while (preg_match($tagRegExp, $this->_content, $tagMatch, PREG_OFFSET_CAPTURE, $nextTag)) {
            $next       = 0;
            $tagHtml    = $tagMatch[0][0];
            $trArr      = array();
            $m          = array();
            $attrRegExp = '#' . $this->_tokenRegex . '#';

            while (preg_match($attrRegExp, $tagHtml, $m, PREG_OFFSET_CAPTURE, $next)) {
                $trArr[] = '{shown:\''.$this->_escape($m[1][0]) . '\','
                    . 'translated:\''.$this->_escape($m[2][0]) . '\','
                    . 'original:\''.$this->_escape($m[3][0]) . '\','
                    . 'location:\'Tag attribute (ALT, TITLE, etc.)\','
                    . 'scope:\''.$this->_escape($m[4][0]) . '\'}';
                $tagHtml = substr_replace($tagHtml, $m[1][0], $m[0][1], strlen($m[0][0]));
                $next = $m[0][1];
            }

            $transRegExp = '# translate='.$quotePatern.'\[(.+?)\]'.$quotePatern.'#i';
            if (preg_match($transRegExp, $tagHtml, $m, PREG_OFFSET_CAPTURE)) {
                foreach ($trArr as $i => $tr) {
                    if (strpos($m[1][0], $tr) !== false) {
                        unset($trArr[$i]);
                    }
                }
                array_unshift($trArr, $m[1][0]);
                $tagHtml = substr_replace($tagHtml, '', $m[0][1], strlen($m[0][0]));
            }
            $trAttr  = ' translate=' . $quoteHtml . '[' . join(',', $trArr) . ']' . $quoteHtml;
            $tagHtml = preg_replace('#' . $tagEndRegexp . '#', $trAttr . '$0', $tagHtml);

            $this->_content = substr_replace($this->_content, $tagHtml, $tagMatch[0][1],
                $tagMatch[9][1]+1-$tagMatch[0][1]);
            $nextTag = $tagMatch[0][1];
        }
    }

    /**
     * Prepare special tags
     *
     */
    protected function _specialTags()
    {
        if ($this->getIsJson()) {
            $quotePatern = '\\\\"';
            $quoteHtml   = '\"';
        } else {
            $quotePatern = '"';
            $quoteHtml   = '"';
        }

        $nextTag = 0;

        $location = array_merge($this->_allowedTagsGlobal, $this->_allowedTagsSimple);
        $tags = implode('|', array_merge(array_keys($this->_allowedTagsGlobal),
            array_keys($this->_allowedTagsSimple)));
        $tagRegExp  = '#<(' . $tags . ')(\s+[^>]*|)(>)#i';

        $tagMatch = array();
        while (preg_match($tagRegExp, $this->_content, $tagMatch, PREG_OFFSET_CAPTURE, $nextTag)) {
            $tagClosure = '</'.$tagMatch[1][0].'>';
            $tagClosurePos = stripos($this->_content, $tagClosure, $tagMatch[0][1]);
            if ($tagClosurePos === false) {
                $tagClosure = '<\/'.$tagMatch[1][0].'>';
                $tagClosurePos = stripos($this->_content, $tagClosure, $tagMatch[0][1]);
            }
            $tagLength = $tagClosurePos-$tagMatch[0][1]+strlen($tagClosure);

            $next       = 0;
            $tagHtml    = substr($this->_content, $tagMatch[0][1], $tagLength);
            $trArr      = array();
            $m          = array();
            while (preg_match('#'.$this->_tokenRegex.'#i', $tagHtml, $m, PREG_OFFSET_CAPTURE, $next)) {
                $trArr[] = '{shown:\''.$this->_escape($m[1][0]).'\','
                    .'translated:\''.$this->_escape($m[2][0]).'\','
                    .'original:\''.$this->_escape($m[3][0]).'\','
                    .'location:\''.$location[strtolower($tagMatch[1][0])].'\','
                    .'scope:\''.$this->_escape($m[4][0]).'\'}';

                $tagHtml = substr_replace($tagHtml, $m[1][0], $m[0][1], strlen($m[0][0]));
                $next    = $m[0][1];
            }
            if (!empty($trArr)) {
                $trArr = array_unique($trArr);
                $tag   = strtolower($tagMatch[1][0]);

                if (in_array($tag, array_keys($this->_allowedTagsGlobal))) {
                    $tagHtml .= '<span class="translate-inline-'.$tag
                        .'" translate='.$quoteHtml.'['.join(',', $trArr).']'.$quoteHtml.'>'.strtoupper($tag).'</span>';
                }
                $this->_content = substr_replace($this->_content, $tagHtml, $tagMatch[0][1], $tagLength);

                if (in_array($tag, array_keys($this->_allowedTagsSimple))) {
                    if (preg_match('# translate='.$quotePatern.'\[(.+?)\]'.$quotePatern.'#i',
                        $tagMatch[0][0], $m, PREG_OFFSET_CAPTURE)
                    ) {
                        foreach ($trArr as $i=>$tr) {
                            if (strpos($m[1][0], $tr)!==false) {
                                unset($trArr[$i]);
                            }
                        }
                        array_unshift($trArr, $m[1][0]);
                        $start = $tagMatch[0][1]+$m[0][1];
                        $len = strlen($m[0][0]);
                    } else {
                        $start = $tagMatch[2][1];
                        $len = 0;
                    }

                    $this->_content = substr_replace($this->_content,
                        ' translate='.$quoteHtml.'['.join(',', $trArr).']'.$quoteHtml, $start, $len);
                }
            }

            $nextTag = $tagMatch[0][1]+10;
        }

    }

    /**
     * Prepare other text inline translates
     *
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
        while (preg_match('#(>|title=\")*('.$this->_tokenRegex.')#', $this->_content, $m, PREG_OFFSET_CAPTURE, $next)) {
            if(-1 == $m[1][1])//title was not found - this is not an attribute
            {
                $tr = '{shown:\''.$this->_escape($m[3][0]).'\','
                    .'translated:\''.$this->_escape($m[4][0]).'\','
                    .'original:\''.$this->_escape($m[5][0]).'\','
                    .'location:\'Text\','
                    .'scope:\''.$this->_escape($m[6][0]).'\'}';
                $spanHtml = '<span translate='.$quoteHtml.'['.$tr.']'.$quoteHtml.'>'.$m[3][0].'</span>';
            }
            else
            {
                $spanHtml = $m[3][0];
            }
            $this->_content = substr_replace($this->_content, $spanHtml, $m[2][1], strlen($m[2][0]) );
            $next = $m[0][1];
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

