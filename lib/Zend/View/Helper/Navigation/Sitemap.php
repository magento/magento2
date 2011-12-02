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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Sitemap.php 20104 2010-01-06 21:26:01Z matthew $
 */

/**
 * @see Zend_View_Helper_Navigation_HelperAbstract
 */
#require_once 'Zend/View/Helper/Navigation/HelperAbstract.php';

/**
 * Helper for printing sitemaps
 *
 * @link http://www.sitemaps.org/protocol.php
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_Navigation_Sitemap
    extends Zend_View_Helper_Navigation_HelperAbstract
{
    /**
     * Namespace for the <urlset> tag
     *
     * @var string
     */
    const SITEMAP_NS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    /**
     * Schema URL
     *
     * @var string
     */
    const SITEMAP_XSD = 'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd';

    /**
     * Whether XML output should be formatted
     *
     * @var bool
     */
    protected $_formatOutput = false;

    /**
     * Whether the XML declaration should be included in XML output
     *
     * @var bool
     */
    protected $_useXmlDeclaration = true;

    /**
     * Whether sitemap should be validated using Zend_Validate_Sitemap_*
     *
     * @var bool
     */
    protected $_useSitemapValidators = true;

    /**
     * Whether sitemap should be schema validated when generated
     *
     * @var bool
     */
    protected $_useSchemaValidation = false;

    /**
     * Server url
     *
     * @var string
     */
    protected $_serverUrl;

    /**
     * View helper entry point:
     * Retrieves helper and optionally sets container to operate on
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               operate on
     * @return Zend_View_Helper_Navigation_Sitemap   fluent interface, returns
     *                                               self
     */
    public function sitemap(Zend_Navigation_Container $container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    // Accessors:

    /**
     * Sets whether XML output should be formatted
     *
     * @param  bool $formatOutput                   [optional] whether output
     *                                              should be formatted. Default
     *                                              is true.
     * @return Zend_View_Helper_Navigation_Sitemap  fluent interface, returns
     *                                              self
     */
    public function setFormatOutput($formatOutput = true)
    {
        $this->_formatOutput = (bool) $formatOutput;
        return $this;
    }

    /**
     * Returns whether XML output should be formatted
     *
     * @return bool  whether XML output should be formatted
     */
    public function getFormatOutput()
    {
        return $this->_formatOutput;
    }

    /**
     * Sets whether the XML declaration should be used in output
     *
     * @param  bool $useXmlDecl                     whether XML delcaration
     *                                              should be rendered
     * @return Zend_View_Helper_Navigation_Sitemap  fluent interface, returns
     *                                              self
     */
    public function setUseXmlDeclaration($useXmlDecl)
    {
        $this->_useXmlDeclaration = (bool) $useXmlDecl;
        return $this;
    }

    /**
     * Returns whether the XML declaration should be used in output
     *
     * @return bool  whether the XML declaration should be used in output
     */
    public function getUseXmlDeclaration()
    {
        return $this->_useXmlDeclaration;
    }

    /**
     * Sets whether sitemap should be validated using Zend_Validate_Sitemap_*
     *
     * @param  bool $useSitemapValidators           whether sitemap validators
     *                                              should be used
     * @return Zend_View_Helper_Navigation_Sitemap  fluent interface, returns
     *                                              self
     */
    public function setUseSitemapValidators($useSitemapValidators)
    {
        $this->_useSitemapValidators = (bool) $useSitemapValidators;
        return $this;
    }

    /**
     * Returns whether sitemap should be validated using Zend_Validate_Sitemap_*
     *
     * @return bool  whether sitemap should be validated using validators
     */
    public function getUseSitemapValidators()
    {
        return $this->_useSitemapValidators;
    }

    /**
     * Sets whether sitemap should be schema validated when generated
     *
     * @param  bool $schemaValidation               whether sitemap should
     *                                              validated using XSD Schema
     * @return Zend_View_Helper_Navigation_Sitemap  fluent interface, returns
     *                                              self
     */
    public function setUseSchemaValidation($schemaValidation)
    {
        $this->_useSchemaValidation = (bool) $schemaValidation;
        return $this;
    }

    /**
     * Returns true if sitemap should be schema validated when generated
     *
     * @return bool
     */
    public function getUseSchemaValidation()
    {
        return $this->_useSchemaValidation;
    }

    /**
     * Sets server url (scheme and host-related stuff without request URI)
     *
     * E.g. http://www.example.com
     *
     * @param  string $serverUrl                    server URL to set (only
     *                                              scheme and host)
     * @throws Zend_Uri_Exception                   if invalid server URL
     * @return Zend_View_Helper_Navigation_Sitemap  fluent interface, returns
     *                                              self
     */
    public function setServerUrl($serverUrl)
    {
        #require_once 'Zend/Uri.php';
        $uri = Zend_Uri::factory($serverUrl);
        $uri->setFragment('');
        $uri->setPath('');
        $uri->setQuery('');

        if ($uri->valid()) {
            $this->_serverUrl = $uri->getUri();
        } else {
            #require_once 'Zend/Uri/Exception.php';
            $e = new Zend_Uri_Exception(sprintf(
                    'Invalid server URL: "%s"',
                    $serverUrl));
            $e->setView($this->view);
            throw $e;
        }

        return $this;
    }

    /**
     * Returns server URL
     *
     * @return string  server URL
     */
    public function getServerUrl()
    {
        if (!isset($this->_serverUrl)) {
            $this->_serverUrl = $this->view->serverUrl();
        }

        return $this->_serverUrl;
    }

    // Helper methods:

    /**
     * Escapes string for XML usage
     *
     * @param  string $string  string to escape
     * @return string          escaped string
     */
    protected function _xmlEscape($string)
    {
        $enc = 'UTF-8';
        if ($this->view instanceof Zend_View_Interface
            && method_exists($this->view, 'getEncoding')
        ) {
            $enc = $this->view->getEncoding();
        }

        // TODO: remove check when minimum PHP version is >= 5.2.3
        if (version_compare(PHP_VERSION, '5.2.3', '>=')) {
            // do not encode existing HTML entities
            return htmlspecialchars($string, ENT_QUOTES, $enc, false);
        } else {
            $string = preg_replace('/&(?!(?:#\d++|[a-z]++);)/ui', '&amp;', $string);
            $string = str_replace(array('<', '>', '\'', '"'), array('&lt;', '&gt;', '&#39;', '&quot;'), $string);
            return $string;
        }
    }

    // Public methods:

    /**
     * Returns an escaped absolute URL for the given page
     *
     * @param  Zend_Navigation_Page $page  page to get URL from
     * @return string
     */
    public function url(Zend_Navigation_Page $page)
    {
        $href = $page->getHref();

        if (!isset($href{0})) {
            // no href
            return '';
        } elseif ($href{0} == '/') {
            // href is relative to root; use serverUrl helper
            $url = $this->getServerUrl() . $href;
        } elseif (preg_match('/^[a-z]+:/im', (string) $href)) {
            // scheme is given in href; assume absolute URL already
            $url = (string) $href;
        } else {
            // href is relative to current document; use url helpers
            $url = $this->getServerUrl()
                 . rtrim($this->view->url(), '/') . '/'
                 . $href;
        }

        return $this->_xmlEscape($url);
    }

    /**
     * Returns a DOMDocument containing the Sitemap XML for the given container
     *
     * @param  Zend_Navigation_Container $container  [optional] container to get
     *                                               breadcrumbs from, defaults
     *                                               to what is registered in the
     *                                               helper
     * @return DOMDocument                           DOM representation of the
     *                                               container
     * @throws Zend_View_Exception                   if schema validation is on
     *                                               and the sitemap is invalid
     *                                               according to the sitemap
     *                                               schema, or if sitemap
     *                                               validators are used and the
     *                                               loc element fails validation
     */
    public function getDomSitemap(Zend_Navigation_Container $container = null)
    {
        if (null === $container) {
            $container = $this->getContainer();
        }

        // check if we should validate using our own validators
        if ($this->getUseSitemapValidators()) {
            #require_once 'Zend/Validate/Sitemap/Changefreq.php';
            #require_once 'Zend/Validate/Sitemap/Lastmod.php';
            #require_once 'Zend/Validate/Sitemap/Loc.php';
            #require_once 'Zend/Validate/Sitemap/Priority.php';

            // create validators
            $locValidator        = new Zend_Validate_Sitemap_Loc();
            $lastmodValidator    = new Zend_Validate_Sitemap_Lastmod();
            $changefreqValidator = new Zend_Validate_Sitemap_Changefreq();
            $priorityValidator   = new Zend_Validate_Sitemap_Priority();
        }

        // create document
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = $this->getFormatOutput();

        // ...and urlset (root) element
        $urlSet = $dom->createElementNS(self::SITEMAP_NS, 'urlset');
        $dom->appendChild($urlSet);

        // create iterator
        $iterator = new RecursiveIteratorIterator($container,
            RecursiveIteratorIterator::SELF_FIRST);

        $maxDepth = $this->getMaxDepth();
        if (is_int($maxDepth)) {
            $iterator->setMaxDepth($maxDepth);
        }
        $minDepth = $this->getMinDepth();
        if (!is_int($minDepth) || $minDepth < 0) {
            $minDepth = 0;
        }

        // iterate container
        foreach ($iterator as $page) {
            if ($iterator->getDepth() < $minDepth || !$this->accept($page)) {
                // page should not be included
                continue;
            }

            // get absolute url from page
            if (!$url = $this->url($page)) {
                // skip page if it has no url (rare case)
                continue;
            }

            // create url node for this page
            $urlNode = $dom->createElementNS(self::SITEMAP_NS, 'url');
            $urlSet->appendChild($urlNode);

            if ($this->getUseSitemapValidators() &&
                !$locValidator->isValid($url)) {
                #require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception(sprintf(
                        'Encountered an invalid URL for Sitemap XML: "%s"',
                        $url));
                $e->setView($this->view);
                throw $e;
            }

            // put url in 'loc' element
            $urlNode->appendChild($dom->createElementNS(self::SITEMAP_NS,
                                                        'loc', $url));

            // add 'lastmod' element if a valid lastmod is set in page
            if (isset($page->lastmod)) {
                $lastmod = strtotime((string) $page->lastmod);

                // prevent 1970-01-01...
                if ($lastmod !== false) {
                    $lastmod = date('c', $lastmod);
                }

                if (!$this->getUseSitemapValidators() ||
                    $lastmodValidator->isValid($lastmod)) {
                    $urlNode->appendChild(
                        $dom->createElementNS(self::SITEMAP_NS, 'lastmod',
                                              $lastmod)
                    );
                }
            }

            // add 'changefreq' element if a valid changefreq is set in page
            if (isset($page->changefreq)) {
                $changefreq = $page->changefreq;
                if (!$this->getUseSitemapValidators() ||
                    $changefreqValidator->isValid($changefreq)) {
                    $urlNode->appendChild(
                        $dom->createElementNS(self::SITEMAP_NS, 'changefreq',
                                              $changefreq)
                    );
                }
            }

            // add 'priority' element if a valid priority is set in page
            if (isset($page->priority)) {
                $priority = $page->priority;
                if (!$this->getUseSitemapValidators() ||
                    $priorityValidator->isValid($priority)) {
                    $urlNode->appendChild(
                        $dom->createElementNS(self::SITEMAP_NS, 'priority',
                                              $priority)
                    );
                }
            }
        }

        // validate using schema if specified
        if ($this->getUseSchemaValidation()) {
            if (!@$dom->schemaValidate(self::SITEMAP_XSD)) {
                #require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception(sprintf(
                        'Sitemap is invalid according to XML Schema at "%s"',
                        self::SITEMAP_XSD));
                $e->setView($this->view);
                throw $e;
            }
        }

        return $dom;
    }

    // Zend_View_Helper_Navigation_Helper:

    /**
     * Renders helper
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::render()}.
     *
     * @param  Zend_Navigation_Container $container  [optional] container to
     *                                               render. Default is to
     *                                               render the container
     *                                               registered in the helper.
     * @return string                                helper output
     */
    public function render(Zend_Navigation_Container $container = null)
    {
        $dom = $this->getDomSitemap($container);

        $xml = $this->getUseXmlDeclaration() ?
               $dom->saveXML() :
               $dom->saveXML($dom->documentElement);

        return rtrim($xml, PHP_EOL);
    }
}
