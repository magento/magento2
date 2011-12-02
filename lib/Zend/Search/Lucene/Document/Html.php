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
 * @package    Zend_Search_Lucene
 * @subpackage Document
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Html.php 23392 2010-11-19 09:53:16Z ramon $
 */


/** Zend_Search_Lucene_Document */
#require_once 'Zend/Search/Lucene/Document.php';


/**
 * HTML document.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Document
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Document_Html extends Zend_Search_Lucene_Document
{
    /**
     * List of document links
     *
     * @var array
     */
    private $_links = array();

    /**
     * List of document header links
     *
     * @var array
     */
    private $_headerLinks = array();

    /**
     * Stored DOM representation
     *
     * @var DOMDocument
     */
    private $_doc;

    /**
     * Exclud nofollow links flag
     *
     * If true then links with rel='nofollow' attribute are not included into
     * document links.
     *
     * @var boolean
     */
    private static $_excludeNoFollowLinks = false;

    /**
     *
     * List of inline tags
     *
     * @var array
     */
    private $_inlineTags = array('a', 'abbr', 'acronym', 'dfn', 'em', 'strong', 'code',
                                'samp', 'kbd', 'var', 'b', 'i', 'big', 'small', 'strike',
                                'tt', 'u', 'font', 'span', 'bdo', 'cite', 'del', 'ins',
                                'q', 'sub', 'sup');

    /**
     * Object constructor
     *
     * @param string  $data         HTML string (may be HTML fragment, )
     * @param boolean $isFile
     * @param boolean $storeContent
     * @param string  $defaultEncoding   HTML encoding, is used if it's not specified using Content-type HTTP-EQUIV meta tag.
     */
    private function __construct($data, $isFile, $storeContent, $defaultEncoding = '')
    {
        $this->_doc = new DOMDocument();
        $this->_doc->substituteEntities = true;

        if ($isFile) {
            $htmlData = file_get_contents($data);
        } else {
            $htmlData = $data;
        }
        @$this->_doc->loadHTML($htmlData);

        if ($this->_doc->encoding === null) {
            // Document encoding is not recognized

            /** @todo improve HTML vs HTML fragment recognition */
            if (preg_match('/<html[^>]*>/i', $htmlData, $matches, PREG_OFFSET_CAPTURE)) {
                // It's an HTML document
                // Add additional HEAD section and recognize document
                $htmlTagOffset = $matches[0][1] + strlen($matches[0][0]);

                @$this->_doc->loadHTML(iconv($defaultEncoding, 'UTF-8//IGNORE', substr($htmlData, 0, $htmlTagOffset))
                                     . '<head><META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=UTF-8"/></head>'
                                     . iconv($defaultEncoding, 'UTF-8//IGNORE', substr($htmlData, $htmlTagOffset)));

                // Remove additional HEAD section
                $xpath = new DOMXPath($this->_doc);
                $head  = $xpath->query('/html/head')->item(0);
                $head->parentNode->removeChild($head);
            } else {
                // It's an HTML fragment
                @$this->_doc->loadHTML('<html><head><META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=UTF-8"/></head><body>'
                                     . iconv($defaultEncoding, 'UTF-8//IGNORE', $htmlData)
                                     . '</body></html>');
            }

        }
        /** @todo Add correction of wrong HTML encoding recognition processing
         * The case is:
         * Content-type HTTP-EQUIV meta tag is presented, but ISO-8859-5 encoding is actually used,
         * even $this->_doc->encoding demonstrates another recognized encoding
         */

        $xpath = new DOMXPath($this->_doc);

        $docTitle = '';
        $titleNodes = $xpath->query('/html/head/title');
        foreach ($titleNodes as $titleNode) {
            // title should always have only one entry, but we process all nodeset entries
            $docTitle .= $titleNode->nodeValue . ' ';
        }
        $this->addField(Zend_Search_Lucene_Field::Text('title', $docTitle, 'UTF-8'));

        $metaNodes = $xpath->query('/html/head/meta[@name]');
        foreach ($metaNodes as $metaNode) {
            $this->addField(Zend_Search_Lucene_Field::Text($metaNode->getAttribute('name'),
                                                           $metaNode->getAttribute('content'),
                                                           'UTF-8'));
        }

        $docBody = '';
        $bodyNodes = $xpath->query('/html/body');
        foreach ($bodyNodes as $bodyNode) {
            // body should always have only one entry, but we process all nodeset entries
            $this->_retrieveNodeText($bodyNode, $docBody);
        }
        if ($storeContent) {
            $this->addField(Zend_Search_Lucene_Field::Text('body', $docBody, 'UTF-8'));
        } else {
            $this->addField(Zend_Search_Lucene_Field::UnStored('body', $docBody, 'UTF-8'));
        }

        $linkNodes = $this->_doc->getElementsByTagName('a');
        foreach ($linkNodes as $linkNode) {
            if (($href = $linkNode->getAttribute('href')) != '' &&
                (!self::$_excludeNoFollowLinks  ||  strtolower($linkNode->getAttribute('rel')) != 'nofollow' )
               ) {
                $this->_links[] = $href;
            }
        }
        $linkNodes = $this->_doc->getElementsByTagName('area');
        foreach ($linkNodes as $linkNode) {
            if (($href = $linkNode->getAttribute('href')) != '' &&
                (!self::$_excludeNoFollowLinks  ||  strtolower($linkNode->getAttribute('rel')) != 'nofollow' )
               ) {
                $this->_links[] = $href;
            }
        }
        $this->_links = array_unique($this->_links);

        $linkNodes = $xpath->query('/html/head/link');
        foreach ($linkNodes as $linkNode) {
            if (($href = $linkNode->getAttribute('href')) != '') {
                $this->_headerLinks[] = $href;
            }
        }
        $this->_headerLinks = array_unique($this->_headerLinks);
    }

    /**
     * Set exclude nofollow links flag
     *
     * @param boolean $newValue
     */
    public static function setExcludeNoFollowLinks($newValue)
    {
        self::$_excludeNoFollowLinks = $newValue;
    }

    /**
     * Get exclude nofollow links flag
     *
     * @return boolean
     */
    public static function getExcludeNoFollowLinks()
    {
        return self::$_excludeNoFollowLinks;
    }

    /**
     * Get node text
     *
     * We should exclude scripts, which may be not included into comment tags, CDATA sections,
     *
     * @param DOMNode $node
     * @param string &$text
     */
    private function _retrieveNodeText(DOMNode $node, &$text)
    {
        if ($node->nodeType == XML_TEXT_NODE) {
            $text .= $node->nodeValue;
            if(!in_array($node->parentNode->tagName, $this->_inlineTags)) {
                $text .= ' ';
            }
        } else if ($node->nodeType == XML_ELEMENT_NODE  &&  $node->nodeName != 'script') {
            foreach ($node->childNodes as $childNode) {
                $this->_retrieveNodeText($childNode, $text);
            }
        }
    }

    /**
     * Get document HREF links
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->_links;
    }

    /**
     * Get document header links
     *
     * @return array
     */
    public function getHeaderLinks()
    {
        return $this->_headerLinks;
    }

    /**
     * Load HTML document from a string
     *
     * @param string  $data
     * @param boolean $storeContent
     * @param string  $defaultEncoding   HTML encoding, is used if it's not specified using Content-type HTTP-EQUIV meta tag.
     * @return Zend_Search_Lucene_Document_Html
     */
    public static function loadHTML($data, $storeContent = false, $defaultEncoding = '')
    {
        return new Zend_Search_Lucene_Document_Html($data, false, $storeContent, $defaultEncoding);
    }

    /**
     * Load HTML document from a file
     *
     * @param string  $file
     * @param boolean $storeContent
     * @param string  $defaultEncoding   HTML encoding, is used if it's not specified using Content-type HTTP-EQUIV meta tag.
     * @return Zend_Search_Lucene_Document_Html
     */
    public static function loadHTMLFile($file, $storeContent = false, $defaultEncoding = '')
    {
        return new Zend_Search_Lucene_Document_Html($file, true, $storeContent, $defaultEncoding);
    }


    /**
     * Highlight text in text node
     *
     * @param DOMText $node
     * @param array   $wordsToHighlight
     * @param callback $callback   Callback method, used to transform (highlighting) text.
     * @param array    $params     Array of additionall callback parameters (first non-optional parameter is a text to transform)
     * @throws Zend_Search_Lucene_Exception
     */
    protected function _highlightTextNode(DOMText $node, $wordsToHighlight, $callback, $params)
    {
        /** Zend_Search_Lucene_Analysis_Analyzer */
        #require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';

        $analyzer = Zend_Search_Lucene_Analysis_Analyzer::getDefault();
        $analyzer->setInput($node->nodeValue, 'UTF-8');

        $matchedTokens = array();

        while (($token = $analyzer->nextToken()) !== null) {
            if (isset($wordsToHighlight[$token->getTermText()])) {
                $matchedTokens[] = $token;
            }
        }

        if (count($matchedTokens) == 0) {
            return;
        }

        $matchedTokens = array_reverse($matchedTokens);

        foreach ($matchedTokens as $token) {
            // Cut text after matched token
            $node->splitText($token->getEndOffset());

            // Cut matched node
            $matchedWordNode = $node->splitText($token->getStartOffset());

            // Retrieve HTML string representation for highlihted word
            $fullCallbackparamsList = $params;
            array_unshift($fullCallbackparamsList, $matchedWordNode->nodeValue);
            $highlightedWordNodeSetHtml = call_user_func_array($callback, $fullCallbackparamsList);

            // Transform HTML string to a DOM representation and automatically transform retrieved string
            // into valid XHTML (It's automatically done by loadHTML() method)
            $highlightedWordNodeSetDomDocument = new DOMDocument('1.0', 'UTF-8');
            $success = @$highlightedWordNodeSetDomDocument->
                                loadHTML('<html><head><meta http-equiv="Content-type" content="text/html; charset=UTF-8"/></head><body>'
                                       . $highlightedWordNodeSetHtml
                                       . '</body></html>');
            if (!$success) {
                #require_once 'Zend/Search/Lucene/Exception.php';
                throw new Zend_Search_Lucene_Exception("Error occured while loading highlighted text fragment: '$highlightedWordNodeSetHtml'.");
            }
            $highlightedWordNodeSetXpath = new DOMXPath($highlightedWordNodeSetDomDocument);
            $highlightedWordNodeSet      = $highlightedWordNodeSetXpath->query('/html/body')->item(0)->childNodes;

            for ($count = 0; $count < $highlightedWordNodeSet->length; $count++) {
                $nodeToImport = $highlightedWordNodeSet->item($count);
                $node->parentNode->insertBefore($this->_doc->importNode($nodeToImport, true /* deep copy */),
                                                $matchedWordNode);
            }

            $node->parentNode->removeChild($matchedWordNode);
        }
    }


    /**
     * highlight words in content of the specified node
     *
     * @param DOMNode $contextNode
     * @param array $wordsToHighlight
     * @param callback $callback   Callback method, used to transform (highlighting) text.
     * @param array    $params     Array of additionall callback parameters (first non-optional parameter is a text to transform)
     */
    protected function _highlightNodeRecursive(DOMNode $contextNode, $wordsToHighlight, $callback, $params)
    {
        $textNodes = array();

        if (!$contextNode->hasChildNodes()) {
            return;
        }

        foreach ($contextNode->childNodes as $childNode) {
            if ($childNode->nodeType == XML_TEXT_NODE) {
                // process node later to leave childNodes structure untouched
                $textNodes[] = $childNode;
            } else {
                // Process node if it's not a script node
                if ($childNode->nodeName != 'script') {
                    $this->_highlightNodeRecursive($childNode, $wordsToHighlight, $callback, $params);
                }
            }
        }

        foreach ($textNodes as $textNode) {
            $this->_highlightTextNode($textNode, $wordsToHighlight, $callback, $params);
        }
    }

    /**
     * Standard callback method used to highlight words.
     *
     * @param  string  $stringToHighlight
     * @return string
     * @internal
     */
    public function applyColour($stringToHighlight, $colour)
    {
        return '<b style="color:black;background-color:' . $colour . '">' . $stringToHighlight . '</b>';
    }

    /**
     * Highlight text with specified color
     *
     * @param string|array $words
     * @param string $colour
     * @return string
     */
    public function highlight($words, $colour = '#66ffff')
    {
        return $this->highlightExtended($words, array($this, 'applyColour'), array($colour));
    }



    /**
     * Highlight text using specified View helper or callback function.
     *
     * @param string|array $words  Words to highlight. Words could be organized using the array or string.
     * @param callback $callback   Callback method, used to transform (highlighting) text.
     * @param array    $params     Array of additionall callback parameters passed through into it
     *                             (first non-optional parameter is an HTML fragment for highlighting)
     * @return string
     * @throws Zend_Search_Lucene_Exception
     */
    public function highlightExtended($words, $callback, $params = array())
    {
        /** Zend_Search_Lucene_Analysis_Analyzer */
        #require_once 'Zend/Search/Lucene/Analysis/Analyzer.php';

        if (!is_array($words)) {
            $words = array($words);
        }

        $wordsToHighlightList = array();
        $analyzer = Zend_Search_Lucene_Analysis_Analyzer::getDefault();
        foreach ($words as $wordString) {
            $wordsToHighlightList[] = $analyzer->tokenize($wordString);
        }
        $wordsToHighlight = call_user_func_array('array_merge', $wordsToHighlightList);

        if (count($wordsToHighlight) == 0) {
            return $this->_doc->saveHTML();
        }

        $wordsToHighlightFlipped = array();
        foreach ($wordsToHighlight as $id => $token) {
            $wordsToHighlightFlipped[$token->getTermText()] = $id;
        }

        if (!is_callable($callback)) {
            #require_once 'Zend/Search/Lucene/Exception.php';
            throw new Zend_Search_Lucene_Exception('$viewHelper parameter mast be a View Helper name, View Helper object or callback.');
        }

        $xpath = new DOMXPath($this->_doc);

        $matchedNodes = $xpath->query("/html/body");
        foreach ($matchedNodes as $matchedNode) {
            $this->_highlightNodeRecursive($matchedNode, $wordsToHighlightFlipped, $callback, $params);
        }
    }


    /**
     * Get HTML
     *
     * @return string
     */
    public function getHTML()
    {
        return $this->_doc->saveHTML();
    }

    /**
     * Get HTML body
     *
     * @return string
     */
    public function getHtmlBody()
    {
        $xpath = new DOMXPath($this->_doc);
        $bodyNodes = $xpath->query('/html/body')->item(0)->childNodes;

        $outputFragments = array();
        for ($count = 0; $count < $bodyNodes->length; $count++) {
            $outputFragments[] = $this->_doc->saveXML($bodyNodes->item($count));
        }

        return implode($outputFragments);
    }
}

