<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper\Navigation;

use DOMDocument;
use RecursiveIteratorIterator;
use Zend\Navigation\AbstractContainer;
use Zend\Navigation\Page\AbstractPage;
use Zend\Stdlib\ErrorHandler;
use Zend\Uri;
use Zend\View;
use Zend\View\Exception;

/**
 * Helper for printing sitemaps
 *
 * @link http://www.sitemaps.org/protocol.php
 */
class Sitemap extends AbstractHelper
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
    protected $formatOutput = false;

    /**
     * Server url
     *
     * @var string
     */
    protected $serverUrl;

    /**
     * List of urls in the sitemap
     *
     * @var array
     */
    protected $urls = array();

    /**
     * Whether sitemap should be validated using Zend\Validate\Sitemap\*
     *
     * @var bool
     */
    protected $useSitemapValidators = true;

    /**
     * Whether sitemap should be schema validated when generated
     *
     * @var bool
     */
    protected $useSchemaValidation = false;

    /**
     * Whether the XML declaration should be included in XML output
     *
     * @var bool
     */
    protected $useXmlDeclaration = true;

    /**
     * Helper entry point
     *
     * @param  string|AbstractContainer $container container to operate on
     * @return Sitemap
     */
    public function __invoke($container = null)
    {
        if (null !== $container) {
            $this->setContainer($container);
        }

        return $this;
    }

    /**
     * Renders helper
     *
     * Implements {@link HelperInterface::render()}.
     *
     * @param  AbstractContainer $container [optional] container to render. Default is
     *                           to render the container registered in the helper.
     * @return string
     */
    public function render($container = null)
    {
        $dom = $this->getDomSitemap($container);
        $xml = $this->getUseXmlDeclaration() ?
            $dom->saveXML() :
            $dom->saveXML($dom->documentElement);

        return rtrim($xml, PHP_EOL);
    }

    /**
     * Returns a DOMDocument containing the Sitemap XML for the given container
     *
     * @param  AbstractContainer                 $container  [optional] container to get
     *                                               breadcrumbs from, defaults
     *                                               to what is registered in the
     *                                               helper
     * @return DOMDocument                           DOM representation of the
     *                                               container
     * @throws Exception\RuntimeException            if schema validation is on
     *                                               and the sitemap is invalid
     *                                               according to the sitemap
     *                                               schema, or if sitemap
     *                                               validators are used and the
     *                                               loc element fails validation
     */
    public function getDomSitemap(AbstractContainer $container = null)
    {
        // Reset the urls
        $this->urls = array();

        if (null === $container) {
            $container = $this->getContainer();
        }

        // check if we should validate using our own validators
        if ($this->getUseSitemapValidators()) {
            // create validators
            $locValidator        = new \Zend\Validator\Sitemap\Loc();
            $lastmodValidator    = new \Zend\Validator\Sitemap\Lastmod();
            $changefreqValidator = new \Zend\Validator\Sitemap\Changefreq();
            $priorityValidator   = new \Zend\Validator\Sitemap\Priority();
        }

        // create document
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = $this->getFormatOutput();

        // ...and urlset (root) element
        $urlSet = $dom->createElementNS(self::SITEMAP_NS, 'urlset');
        $dom->appendChild($urlSet);

        // create iterator
        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);

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
                // or already is in the sitemap
                continue;
            }

            // create url node for this page
            $urlNode = $dom->createElementNS(self::SITEMAP_NS, 'url');
            $urlSet->appendChild($urlNode);

            if ($this->getUseSitemapValidators()
                && !$locValidator->isValid($url)
            ) {
                throw new Exception\RuntimeException(sprintf(
                    'Encountered an invalid URL for Sitemap XML: "%s"',
                    $url
                ));
            }

            // put url in 'loc' element
            $urlNode->appendChild($dom->createElementNS(self::SITEMAP_NS, 'loc', $url));

            // add 'lastmod' element if a valid lastmod is set in page
            if (isset($page->lastmod)) {
                $lastmod = strtotime((string) $page->lastmod);

                // prevent 1970-01-01...
                if ($lastmod !== false) {
                    $lastmod = date('c', $lastmod);
                }

                if (!$this->getUseSitemapValidators()
                    || $lastmodValidator->isValid($lastmod)
                ) {
                    // Cast $lastmod to string in case no validation was used
                    $urlNode->appendChild(
                        $dom->createElementNS(self::SITEMAP_NS, 'lastmod', (string) $lastmod)
                    );
                }
            }

            // add 'changefreq' element if a valid changefreq is set in page
            if (isset($page->changefreq)) {
                $changefreq = $page->changefreq;
                if (!$this->getUseSitemapValidators() ||
                    $changefreqValidator->isValid($changefreq)) {
                    $urlNode->appendChild(
                        $dom->createElementNS(self::SITEMAP_NS, 'changefreq', $changefreq)
                    );
                }
            }

            // add 'priority' element if a valid priority is set in page
            if (isset($page->priority)) {
                $priority = $page->priority;
                if (!$this->getUseSitemapValidators() ||
                    $priorityValidator->isValid($priority)) {
                    $urlNode->appendChild(
                        $dom->createElementNS(self::SITEMAP_NS, 'priority', $priority)
                    );
                }
            }
        }

        // validate using schema if specified
        if ($this->getUseSchemaValidation()) {
            ErrorHandler::start();
            $test  = $dom->schemaValidate(self::SITEMAP_XSD);
            $error = ErrorHandler::stop();
            if (!$test) {
                throw new Exception\RuntimeException(sprintf(
                    'Sitemap is invalid according to XML Schema at "%s"',
                    self::SITEMAP_XSD
                ), 0, $error);
            }
        }

        return $dom;
    }

    /**
     * Returns an escaped absolute URL for the given page
     *
     * @param  AbstractPage $page
     * @return string
     */
    public function url(AbstractPage $page)
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
            $basePathHelper = $this->getView()->plugin('basepath');
            $curDoc         = $basePathHelper();
            $curDoc         = ('/' == $curDoc) ? '' : trim($curDoc, '/');
            $url            = rtrim($this->getServerUrl(), '/') . '/'
                                                                . $curDoc
                                                                . (empty($curDoc) ? '' : '/') . $href;
        }

        if (! in_array($url, $this->urls)) {
            $this->urls[] = $url;
            return $this->xmlEscape($url);
        }

        return;
    }

    /**
     * Escapes string for XML usage
     *
     * @param  string $string
     * @return string
     */
    protected function xmlEscape($string)
    {
        $escaper = $this->view->plugin('escapeHtml');
        return $escaper($string);
    }

    /**
     * Sets whether XML output should be formatted
     *
     * @param  bool $formatOutput
     * @return Sitemap
     */
    public function setFormatOutput($formatOutput = true)
    {
        $this->formatOutput = (bool) $formatOutput;
        return $this;
    }

    /**
     * Returns whether XML output should be formatted
     *
     * @return bool
     */
    public function getFormatOutput()
    {
        return $this->formatOutput;
    }

    /**
     * Sets server url (scheme and host-related stuff without request URI)
     *
     * E.g. http://www.example.com
     *
     * @param  string $serverUrl
     * @return Sitemap
     * @throws Exception\InvalidArgumentException
     */
    public function setServerUrl($serverUrl)
    {
        $uri = Uri\UriFactory::factory($serverUrl);
        $uri->setFragment('');
        $uri->setPath('');
        $uri->setQuery('');

        if ($uri->isValid()) {
            $this->serverUrl = $uri->toString();
        } else {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid server URL: "%s"',
                $serverUrl
            ));
        }

        return $this;
    }

    /**
     * Returns server URL
     *
     * @return string
     */
    public function getServerUrl()
    {
        if (!isset($this->serverUrl)) {
            $serverUrlHelper  = $this->getView()->plugin('serverUrl');
            $this->serverUrl = $serverUrlHelper();
        }

        return $this->serverUrl;
    }

    /**
     * Sets whether sitemap should be validated using Zend\Validate\Sitemap_*
     *
     * @param  bool $useSitemapValidators
     * @return Sitemap
     */
    public function setUseSitemapValidators($useSitemapValidators)
    {
        $this->useSitemapValidators = (bool) $useSitemapValidators;
        return $this;
    }

    /**
     * Returns whether sitemap should be validated using Zend\Validate\Sitemap_*
     *
     * @return bool
     */
    public function getUseSitemapValidators()
    {
        return $this->useSitemapValidators;
    }

    /**
     * Sets whether sitemap should be schema validated when generated
     *
     * @param  bool $schemaValidation
     * @return Sitemap
     */
    public function setUseSchemaValidation($schemaValidation)
    {
        $this->useSchemaValidation = (bool) $schemaValidation;
        return $this;
    }

    /**
     * Returns true if sitemap should be schema validated when generated
     *
     * @return bool
     */
    public function getUseSchemaValidation()
    {
        return $this->useSchemaValidation;
    }

    /**
     * Sets whether the XML declaration should be used in output
     *
     * @param  bool $useXmlDecl
     * @return Sitemap
     */
    public function setUseXmlDeclaration($useXmlDecl)
    {
        $this->useXmlDeclaration = (bool) $useXmlDecl;
        return $this;
    }

    /**
     * Returns whether the XML declaration should be used in output
     *
     * @return bool
     */
    public function getUseXmlDeclaration()
    {
        return $this->useXmlDeclaration;
    }
}
