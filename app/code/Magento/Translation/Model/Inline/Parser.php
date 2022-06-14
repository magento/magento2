<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Model\Inline;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\Translate\Inline\ParserInterface;
use Magento\Translation\Model\ResourceModel\StringFactory;
use Magento\Translation\Model\ResourceModel\StringUtils;
use Magento\Translation\Model\ResourceModel\StringUtilsFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\Escaper;

/**
 * Parses content and applies necessary html element wrapping and client scripts for inline translation.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Parser implements ParserInterface
{
    /**
     * data-translate html element attribute name
     */
    public const DATA_TRANSLATE = 'data-translate';

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Response body or JSON content string
     *
     * @var string
     */
    protected $_content;

    /**
     * Current content is JSON or Response body
     *
     * @var bool
     */
    protected $_isJson = false;

    /**
     * Get max translate block in same tag
     *
     * @var int
     */
    protected $_maxTranslateBlocks = 7;

    /**
     * List of global tags
     *
     * @var array
     */
    protected $_allowedTagsGlobal = ['script' => 'String in Javascript', 'title' => 'Page title'];

    /**
     * List of simple tags
     *
     * @var array
     */
    protected $_allowedTagsSimple = [
        'legend' => 'Caption for the fieldset element',
        'label' => 'Label for an input element.',
        'button' => 'Push button',
        'a' => 'Link label',
        'b' => 'Bold text',
        'strong' => 'Strong emphasized text',
        'i' => 'Italic text',
        'em' => 'Emphasized text',
        'u' => 'Underlined text',
        'sup' => 'Superscript text',
        'sub' => 'Subscript text',
        'span' => 'Span element',
        'small' => 'Smaller text',
        'big' => 'Bigger text',
        'address' => 'Contact information',
        'blockquote' => 'Long quotation',
        'q' => 'Short quotation',
        'cite' => 'Citation',
        'caption' => 'Table caption',
        'abbr' => 'Abbreviated phrase',
        'acronym' => 'An acronym',
        'var' => 'Variable part of a text',
        'dfn' => 'Term',
        'strike' => 'Strikethrough text',
        'del' => 'Deleted text',
        'ins' => 'Inserted text',
        'h1' => 'Heading level 1',
        'h2' => 'Heading level 2',
        'h3' => 'Heading level 3',
        'h4' => 'Heading level 4',
        'h5' => 'Heading level 5',
        'h6' => 'Heading level 6',
        'center' => 'Centered text',
        'select' => 'List options',
        'img' => 'Image',
        'input' => 'Form element',
    ];

    /**
     * @var StringFactory
     */
    protected $_resourceFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Zend_Filter_Interface
     */
    protected $_inputFilter;

    /**
     * @var State
     */
    protected $_appState;

    /**
     * @var InlineInterface
     */
    protected $_translateInline;

    /**
     * @var TypeListInterface
     */
    protected $_appCache;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var array
     */
    private $relatedCacheTypes;

    /**
     * Initialize base inline translation model
     *
     * @param StringUtilsFactory $resource
     * @param StoreManagerInterface $storeManager
     * @param \Zend_Filter_Interface $inputFilter
     * @param State $appState
     * @param TypeListInterface $appCache
     * @param InlineInterface $translateInline
     * @param Escaper $escaper
     * @param CacheManager $cacheManager
     * @param array $relatedCacheTypes
     */
    public function __construct(
        StringUtilsFactory $resource,
        StoreManagerInterface $storeManager,
        \Zend_Filter_Interface $inputFilter,
        State $appState,
        TypeListInterface $appCache,
        InlineInterface $translateInline,
        Escaper $escaper,
        CacheManager $cacheManager,
        array $relatedCacheTypes = []
    ) {
        $this->_resourceFactory = $resource;
        $this->_storeManager = $storeManager;
        $this->_inputFilter = $inputFilter;
        $this->_appState = $appState;
        $this->_appCache = $appCache;
        $this->_translateInline = $translateInline;
        $this->escaper = $escaper;
        $this->cacheManager = $cacheManager;
        $this->relatedCacheTypes = $relatedCacheTypes;
    }

    /**
     * Parse and save edited translation
     *
     * @param array $translateParams
     *
     * @return array
     */
    public function processAjaxPost(array $translateParams)
    {
        if (!$this->_translateInline->isAllowed()) {
            return ['inline' => 'not allowed'];
        }

        if (!empty($this->relatedCacheTypes)) {
            $this->_appCache->invalidate($this->relatedCacheTypes);
        }

        $this->_validateTranslationParams($translateParams);
        $this->_filterTranslationParams($translateParams, ['custom']);

        /** @var $validStoreId int */
        $validStoreId = $this->_storeManager->getStore()->getId();

        /** @var $resource StringUtils */
        $resource = $this->_resourceFactory->create();
        foreach ($translateParams as $param) {
            if ($this->_appState->getAreaCode() == FrontNameResolver::AREA_CODE) {
                $storeId = 0;
            } elseif (empty($param['perstore'])) {
                $resource->deleteTranslate($param['original'], null, false);
                $storeId = 0;
            } else {
                $storeId = $validStoreId;
            }
            $resource->saveTranslate(
                $param['original'],
                $param['custom'],
                null,
                $storeId
            );
        }

        return $this->cacheManager->updateAndGetTranslations();
    }

    /**
     * Validate the structure of translation parameters
     *
     * @param array $translateParams
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _validateTranslationParams(array $translateParams)
    {
        foreach ($translateParams as $param) {
            if (!is_array($param) || !isset($param['original']) || !isset($param['custom'])) {
                throw new \InvalidArgumentException(
                    'Both original and custom phrases are required for inline translation.'
                );
            }
        }
    }

    /**
     * Apply input filter to values of translation parameters
     *
     * @param array $translateParams
     * @param array $fieldNames Names of fields values of which are to be filtered
     *
     * @return void
     */
    protected function _filterTranslationParams(array &$translateParams, array $fieldNames)
    {
        foreach ($translateParams as &$param) {
            foreach ($fieldNames as $fieldName) {
                $param[$fieldName] = $this->_inputFilter->filter($param[$fieldName]);
            }
        }
    }

    /**
     * Replace html body with translation wrapping.
     *
     * @param string $body
     *
     * @return string
     */
    public function processResponseBodyString($body)
    {
        $this->_content = $body;

        $this->_specialTags();
        $this->_tagAttributes();
        $this->_otherText();

        return $this->_content;
    }

    /**
     * Returns the body content that is being parsed.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Sets the body content that is being parsed passed upon the passed in string.
     *
     * @param string $content
     *
     * @return void
     */
    public function setContent($content)
    {
        $this->_content = $content;
    }

    /**
     * Set flag about parsed content is Json
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function setIsJson($flag)
    {
        $this->_isJson = $flag;
        return $this;
    }

    /**
     * Get attribute location.
     *
     * @param array $matches
     * @param array $options
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAttributeLocation($matches, $options)
    {
        // return value should not be translated.
        return 'Tag attribute (ALT, TITLE, etc.)';
    }

    /**
     * Get tag location
     *
     * @param array $matches
     * @param array $options
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getTagLocation($matches, $options)
    {
        $tagName = isset($options['tagName']) ? strtolower($options['tagName']) : '';

        return $options['tagList'][$tagName] ?? (ucfirst($tagName) . ' Text');
    }

    /**
     * Format translation for special tags. Adding translate mode attribute for vde requests.
     *
     * @param string $tagHtml
     * @param string $tagName
     * @param array $trArr
     *
     * @return string
     */
    protected function _applySpecialTagsFormat($tagHtml, $tagName, $trArr)
    {
        $specialTags = $tagHtml . '<span class="translate-inline-' . $tagName . '" ' . $this->_getHtmlAttribute(
            self::DATA_TRANSLATE,
            '[' . $this->escaper->escapeHtml(join(',', $trArr)) . ']'
        );
        $additionalAttr = $this->_getAdditionalHtmlAttribute($tagName);
        if ($additionalAttr !== null) {
            $specialTags .= ' ' . $additionalAttr . '>';
        } else {
            $specialTags .= '>' . strtoupper($tagName);
        }
        $specialTags .= '</span>';

        return $specialTags;
    }

    /**
     * Format translation for simple tags.  Added translate mode attribute for vde requests.
     *
     * @param string $tagHtml
     * @param string $tagName
     * @param array $trArr
     *
     * @return string
     */
    protected function _applySimpleTagsFormat($tagHtml, $tagName, $trArr)
    {
        $tagHtml = $tagHtml !== null ? $tagHtml : '';
        $tagName = $tagName !== null ? $tagName : '';
        $simpleTags = substr(
            $tagHtml,
            0,
            strlen($tagName) + 1
        ) . ' ' . $this->_getHtmlAttribute(
            self::DATA_TRANSLATE,
            $this->escaper->escapeHtml('[' . join(',', $trArr) . ']')
        );
        $additionalAttr = $this->_getAdditionalHtmlAttribute($tagName);
        if ($additionalAttr !== null) {
            $simpleTags .= ' ' . $additionalAttr;
        }
        $simpleTags .= substr($tagHtml, strlen($tagName) + 1);

        return $simpleTags;
    }

    /**
     * Get translate data by regexp
     *
     * @param string $regexp
     * @param string $text
     * @param callable $locationCallback
     * @param array $options
     *
     * @return array
     */
    private function _getTranslateData(string $regexp, string &$text, callable $locationCallback, array $options = [])
    {
        $trArr = [];
        $next = 0;
        while (preg_match($regexp, $text, $matches, PREG_OFFSET_CAPTURE, $next)) {
            $trArr[] = json_encode(
                [
                    'shown' => htmlspecialchars_decode($matches[1][0]),
                    'translated' => htmlspecialchars_decode($matches[2][0]),
                    'original' => htmlspecialchars_decode($matches[3][0]),
                    'location' => htmlspecialchars_decode($locationCallback($matches, $options)),
                ]
            );

            if (!str_contains($text, 'text/x-magento-init')) {
                $text = substr_replace($text, $matches[1][0], $matches[0][1], strlen($matches[0][0]));
            } else {
                $text = substr_replace($text, $matches[3][0], $matches[0][1], strlen($matches[0][0]));
            }
            $next = $matches[0][1];
        }
        return $trArr;
    }

    /**
     * Prepare tags inline translates
     *
     * @return void
     */
    private function _tagAttributes()
    {
        $this->_prepareTagAttributesForContent($this->_content);
    }

    /**
     * Prepare tags inline translates for the content
     *
     * @param string &$content
     *
     * @return void
     */
    private function _prepareTagAttributesForContent(&$content)
    {
        $quoteHtml = $this->_getHtmlQuote();
        $tagMatch = [];
        $nextTag = 0;
        $tagRegExp = '#<([a-z]+)\s*?[^>]+?((' . self::REGEXP_TOKEN . ')[^>]*?)+\\\\?/?>#iS';
        while (preg_match($tagRegExp, $content, $tagMatch, PREG_OFFSET_CAPTURE, $nextTag)) {
            $tagHtml = $tagMatch[0][0];
            $matches = [];
            $attrRegExp = '#' . self::REGEXP_TOKEN . '#S';
            $trArr = $this->_getTranslateData($attrRegExp, $tagHtml, [$this, '_getAttributeLocation']);
            if ($trArr) {
                $transRegExp = '# ' . $this->_getHtmlAttribute(
                    self::DATA_TRANSLATE,
                    '\[([^' . preg_quote($quoteHtml) . ']*)]'
                ) . '#i';
                if (preg_match($transRegExp, $tagHtml, $matches)) {
                    $tagHtml = str_replace($matches[0], '', $tagHtml);
                    $trAttr = ' ' . $this->_getHtmlAttribute(
                        self::DATA_TRANSLATE,
                        '[' . $this->escaper->escapeHtml($matches[1]) . ',' .
                        str_replace("\"", "'", join(',', $trArr)) . ']'
                    );
                } else {
                    $trAttr = ' ' . $this->_getHtmlAttribute(
                        self::DATA_TRANSLATE,
                        '[' . str_replace("\"", "&quot;", join(',', $trArr)) . ']'
                    );
                }
                $trAttr = $this->_addTranslateAttribute($trAttr);

                $tagHtml = substr_replace($tagHtml, $trAttr, strlen($tagMatch[1][0]) + 1, 1);
                $content = substr_replace($content, $tagHtml, $tagMatch[0][1], strlen($tagMatch[0][0]));
            }
            $nextTag = $tagMatch[0][1] + strlen($tagHtml);
        }
    }

    /**
     * Get html element attribute
     *
     * @param string $name
     * @param string $value
     *
     * @return string
     */
    private function _getHtmlAttribute($name, $value)
    {
        return $name . '=' . $this->_getHtmlQuote() . $value . $this->_getHtmlQuote();
    }

    /**
     * Add data-translate-mode attribute
     *
     * @param string $trAttr
     *
     * @return string
     */
    private function _addTranslateAttribute($trAttr)
    {
        $translateAttr = $trAttr;
        $additionalAttr = $this->_getAdditionalHtmlAttribute();
        if ($additionalAttr !== null) {
            $translateAttr .= ' ' . $additionalAttr . ' ';
        }

        return $translateAttr;
    }

    /**
     * Get html quote symbol
     *
     * @return string
     */
    private function _getHtmlQuote()
    {
        if ($this->_isJson) {
            return '\"';
        }

        return '"';
    }

    /**
     * Prepare special tags
     *
     * @return void
     */
    private function _specialTags()
    {
        $this->_translateTags(
            $this->_content,
            $this->_allowedTagsGlobal,
            function ($tagHtml, $tagName, $trArr) {
                return $this->_applySpecialTagsFormat($tagHtml, $tagName, $trArr);
            }
        );
        $this->_translateTags(
            $this->_content,
            $this->_allowedTagsSimple,
            function ($tagHtml, $tagName, $trArr) {
                return $this->_applySimpleTagsFormat($tagHtml, $tagName, $trArr);
            }
        );
    }

    /**
     * Prepare simple tags
     *
     * @param string $content
     * @param array $tagsList
     * @param callable $formatCallback
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function _translateTags(string &$content, array $tagsList, callable $formatCallback)
    {
        $nextTag = 0;
        $tagRegExpBody = '#<(body)(/?>| \s*[^>]*+/?>)#iSU';

        $tags = implode('|', array_keys($tagsList));
        $tagRegExp = '#<(' . $tags . ')(/?>| \s*[^>]*+/?>)#iSU';
        $tagMatch = [];
        $headTranslateTags = '';
        while (preg_match($tagRegExp, $content, $tagMatch, PREG_OFFSET_CAPTURE, $nextTag)) {
            $tagName = strtolower($tagMatch[1][0]);
            if (substr($tagMatch[0][0], -2) == '/>') {
                $tagClosurePos = $tagMatch[0][1] + strlen($tagMatch[0][0]);
            } else {
                $tagClosurePos = $this->_findEndOfTag($content, $tagName, $tagMatch[0][1]);
            }

            if ($tagClosurePos === false) {
                $nextTag += strlen($tagMatch[0][0]);
                continue;
            }

            $tagLength = $tagClosurePos - $tagMatch[0][1];

            $tagStartLength = strlen($tagMatch[0][0]);

            $tagHtml = $tagMatch[0][0] . substr(
                $content,
                $tagMatch[0][1] + $tagStartLength,
                $tagLength - $tagStartLength
            );
            $tagClosurePos = $tagMatch[0][1] + strlen($tagHtml);

            $trArr = $this->_getTranslateData(
                '#' . self::REGEXP_TOKEN . '#iS',
                $tagHtml,
                [$this, '_getTagLocation'],
                ['tagName' => $tagName, 'tagList' => $tagsList]
            );

            if (!empty($trArr)) {
                $trArr = array_unique($trArr);

                $tagBodyMatch = [];
                preg_match($tagRegExpBody, $content, $tagBodyMatch, PREG_OFFSET_CAPTURE);
                if (!empty($tagBodyMatch)) {
                    $tagBodyOpenStartPosition = $tagBodyMatch[0][1];

                    if (array_key_exists($tagName, $this->_allowedTagsGlobal)
                        && $tagBodyOpenStartPosition > $tagMatch[0][1]
                    ) {
                        $tagHtmlHead = $formatCallback($tagHtml, $tagName, $trArr);
                        $headTranslateTags .= $tagHtmlHead !== null ? substr($tagHtmlHead, strlen($tagHtml)) : '';
                    } else {
                        $tagHtml = $formatCallback($tagHtml, $tagName, $trArr);
                    }
                }

                $tagClosurePos = $tagMatch[0][1] + strlen($tagHtml);
                $content = substr_replace($content, $tagHtml, $tagMatch[0][1], $tagLength);
            }
            $nextTag = $tagClosurePos;
        }
        if ($headTranslateTags) {
            $tagBodyMatch = [];
            preg_match($tagRegExpBody, $content, $tagBodyMatch, PREG_OFFSET_CAPTURE);
            $tagBodyOpenStartPosition = $tagBodyMatch[0][1];
            $openTagBodyEndPosition = $tagBodyOpenStartPosition + strlen($tagBodyMatch[0][0]);
            $content = substr($content, 0, $openTagBodyEndPosition)
                . $headTranslateTags
                . substr($content, $openTagBodyEndPosition);
        }
    }

    /**
     * Find end of tag
     *
     * @param string $body
     * @param string $tagName
     * @param int $from
     *
     * @return bool|int return false if end of tag is not found
     */
    private function _findEndOfTag($body, $tagName, $from)
    {
        $body = $body !== null ? $body : '';
        $openTag = '<' . $tagName;
        $closeTag = ($this->_isJson ? '<\\/' : '</') . $tagName;
        $tagLength = strlen($tagName);
        $length = $tagLength + 1;
        $end = $from + 1;
        while (substr_count($body, $openTag, $from, $length) !== substr_count($body, $closeTag, $from, $length)) {
            $end = strpos($body, $closeTag, $end + $tagLength + 1);
            if ($end === false) {
                return false;
            }
            $length = $end - $from + $tagLength + 3;
        }
        if (preg_match('#<\\\\?\/' . $tagName . '\s*?>#i', $body, $tagMatch, 0, $end)) {
            return $end + strlen($tagMatch[0]);
        }

        return false;
    }

    /**
     * Prepare other text inline translates
     *
     * @return void
     */
    private function _otherText()
    {
        $next = 0;
        $matches = [];
        while (preg_match('#' . self::REGEXP_TOKEN . '#', $this->_content, $matches, PREG_OFFSET_CAPTURE, $next)) {
            $translateProperties = json_encode(
                [
                    'shown' => $matches[1][0],
                    'translated' => $matches[2][0],
                    'original' => $matches[3][0],
                    'location' => 'Text',
                    'scope' => $matches[4][0],
                ],
                JSON_HEX_QUOT
            );

            $spanHtml = $this->_getDataTranslateSpan(
                '[' . $this->escaper->escapeHtmlAttr($translateProperties) . ']',
                $matches[1][0]
            );
            $this->_content = substr_replace($this->_content, $spanHtml, $matches[0][1], strlen($matches[0][0]));
            $next = $matches[0][1] + strlen($spanHtml) - 1;
        }
    }

    /**
     * Returns the html span that contains the data translate attribute including vde specific translate mode attribute
     *
     * @param string $data
     * @param string $text
     *
     * @return string
     */
    protected function _getDataTranslateSpan($data, $text)
    {
        $translateSpan = '<span ' . $this->_getHtmlAttribute(self::DATA_TRANSLATE, $data);
        $additionalAttr = $this->_getAdditionalHtmlAttribute();
        if ($additionalAttr !== null) {
            $translateSpan .= ' ' . $additionalAttr;
        }
        $translateSpan .= '>' . $text . '</span>';
        return $translateSpan;
    }

    /**
     * Add an additional html attribute if needed.
     *
     * @param mixed $tagName
     *
     * @return string
     */
    protected function _getAdditionalHtmlAttribute($tagName = null)
    {
        return $this->_translateInline->getAdditionalHtmlAttribute($tagName);
    }
}
