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
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Technorati.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/**
 * Zend_Service_Technorati provides an easy, intuitive and object-oriented interface
 * for using the Technorati API.
 *
 * It provides access to all available Technorati API queries
 * and returns the original XML response as a friendly PHP object.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Technorati
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Technorati
{
    /** Base Technorati API URI */
    const API_URI_BASE = 'http://api.technorati.com';

    /** Query paths */
    const API_PATH_COSMOS           = '/cosmos';
    const API_PATH_SEARCH           = '/search';
    const API_PATH_TAG              = '/tag';
    const API_PATH_DAILYCOUNTS      = '/dailycounts';
    const API_PATH_TOPTAGS          = '/toptags';
    const API_PATH_BLOGINFO         = '/bloginfo';
    const API_PATH_BLOGPOSTTAGS     = '/blogposttags';
    const API_PATH_GETINFO          = '/getinfo';
    const API_PATH_KEYINFO          = '/keyinfo';

    /** Prevent magic numbers */
    const PARAM_LIMIT_MIN_VALUE = 1;
    const PARAM_LIMIT_MAX_VALUE = 100;
    const PARAM_DAYS_MIN_VALUE  = 1;
    const PARAM_DAYS_MAX_VALUE  = 180;
    const PARAM_START_MIN_VALUE = 1;


    /**
     * Technorati API key
     *
     * @var     string
     * @access  protected
     */
    protected $_apiKey;

    /**
     * Zend_Rest_Client instance
     *
     * @var     Zend_Rest_Client
     * @access  protected
     */
    protected $_restClient;


    /**
     * Constructs a new Zend_Service_Technorati instance
     * and setup character encoding.
     *
     * @param  string $apiKey  Your Technorati API key
     */
    public function __construct($apiKey)
    {
        iconv_set_encoding('output_encoding', 'UTF-8');
        iconv_set_encoding('input_encoding', 'UTF-8');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        $this->_apiKey = $apiKey;
    }


    /**
     * Cosmos query lets you see what blogs are linking to a given URL.
     *
     * On the Technorati site, you can enter a URL in the searchbox and
     * it will return a list of blogs linking to it.
     * The API version allows more features and gives you a way
     * to use the cosmos on your own site.
     *
     * Query options include:
     *
     * 'type'       => (link|weblog)
     *      optional - A value of link returns the freshest links referencing your target URL.
     *      A value of weblog returns the last set of unique weblogs referencing your target URL.
     * 'limit'      => (int)
     *      optional - adjust the size of your result from the default value of 20
     *      to between 1 and 100 results.
     * 'start'      => (int)
     *      optional - adjust the range of your result set.
     *      Set this number to larger than zero and you will receive
     *      the portion of Technorati's total result set ranging from start to start+limit.
     *      The default start value is 1.
     * 'current'    => (true|false)
     *      optional - the default setting of true
     *      Technorati returns links that are currently on a weblog's homepage.
     *      Set this parameter to false if you would like to receive all links
     *      to the given URL regardless of their current placement on the source blog.
     *      Internally the value is converted in (yes|no).
     * 'claim'      => (true|false)
     *      optional - the default setting of FALSE returns no user information
     *      about each weblog included in the result set when available.
     *      Set this parameter to FALSE to include Technorati member data
     *      in the result set when a weblog in your result set
     *      has been successfully claimed by a member of Technorati.
     *      Internally the value is converted in (int).
     * 'highlight'  => (true|false)
     *      optional - the default setting of TRUE
     *      highlights the citation of the given URL within the weblog excerpt.
     *      Set this parameter to FALSE to apply no special markup to the blog excerpt.
     *      Internally the value is converted in (int).
     *
     * @param   string $url     the URL you are searching for. Prefixes http:// and www. are optional.
     * @param   array $options  additional parameters to refine your query
     * @return  Zend_Service_Technorati_CosmosResultSet
     * @throws  Zend_Service_Technorati_Exception
     * @link    http://technorati.com/developers/api/cosmos.html Technorati API: Cosmos Query reference
     */
    public function cosmos($url, $options = null)
    {
        static $defaultOptions = array( 'type'      => 'link',
                                        'start'     => 1,
                                        'limit'     => 20,
                                        'current'   => 'yes',
                                        'format'    => 'xml',
                                        'claim'     => 0,
                                        'highlight' => 1,
                                        );

        $options['url'] = $url;

        $options = $this->_prepareOptions($options, $defaultOptions);
        $this->_validateCosmos($options);
        $response = $this->_makeRequest(self::API_PATH_COSMOS, $options);
        $dom = $this->_convertResponseAndCheckContent($response);

        /**
         * @see Zend_Service_Technorati_CosmosResultSet
         */
        #require_once 'Zend/Service/Technorati/CosmosResultSet.php';
        return new Zend_Service_Technorati_CosmosResultSet($dom, $options);
    }

    /**
     * Search lets you see what blogs contain a given search string.
     *
     * Query options include:
     *
     * 'language'   => (string)
     *      optional - a ISO 639-1 two character language code
     *      to retrieve results specific to that language.
     *      This feature is currently beta and may not work for all languages.
     * 'authority'  => (n|a1|a4|a7)
     *      optional - filter results to those from blogs with at least
     *      the Technorati Authority specified.
     *      Technorati calculates a blog's authority by how many people link to it.
     *      Filtering by authority is a good way to refine your search results.
     *      There are four settings:
     *      - n  => Any authority: All results.
     *      - a1 => A little authority: Results from blogs with at least one link.
     *      - a4 => Some authority: Results from blogs with a handful of links.
     *      - a7 => A lot of authority: Results from blogs with hundreds of links.
     * 'limit'      => (int)
     *      optional - adjust the size of your result from the default value of 20
     *      to between 1 and 100 results.
     * 'start'      => (int)
     *      optional - adjust the range of your result set.
     *      Set this number to larger than zero and you will receive
     *      the portion of Technorati's total result set ranging from start to start+limit.
     *      The default start value is 1.
     * 'claim'      => (true|false)
     *      optional - the default setting of FALSE returns no user information
     *      about each weblog included in the result set when available.
     *      Set this parameter to FALSE to include Technorati member data
     *      in the result set when a weblog in your result set
     *      has been successfully claimed by a member of Technorati.
     *      Internally the value is converted in (int).
     *
     * @param   string $query   the words you are searching for.
     * @param   array $options  additional parameters to refine your query
     * @return  Zend_Service_Technorati_SearchResultSet
     * @throws  Zend_Service_Technorati_Exception
     * @link    http://technorati.com/developers/api/search.html Technorati API: Search Query reference
     */
    public function search($query, $options = null)
    {
        static $defaultOptions = array( 'start'     => 1,
                                        'limit'     => 20,
                                        'format'    => 'xml',
                                        'claim'     => 0);

        $options['query'] = $query;

        $options = $this->_prepareOptions($options, $defaultOptions);
        $this->_validateSearch($options);
        $response = $this->_makeRequest(self::API_PATH_SEARCH, $options);
        $dom = $this->_convertResponseAndCheckContent($response);

        /**
         * @see Zend_Service_Technorati_SearchResultSet
         */
        #require_once 'Zend/Service/Technorati/SearchResultSet.php';
        return new Zend_Service_Technorati_SearchResultSet($dom, $options);
    }

    /**
     * Tag lets you see what posts are associated with a given tag.
     *
     * Query options include:
     *
     * 'limit'          => (int)
     *      optional - adjust the size of your result from the default value of 20
     *      to between 1 and 100 results.
     * 'start'          => (int)
     *      optional - adjust the range of your result set.
     *      Set this number to larger than zero and you will receive
     *      the portion of Technorati's total result set ranging from start to start+limit.
     *      The default start value is 1.
     * 'excerptsize'    => (int)
     *      optional - number of word characters to include in the post excerpts.
     *      By default 100 word characters are returned.
     * 'topexcerptsize' => (int)
     *      optional - number of word characters to include in the first post excerpt.
     *      By default 150 word characters are returned.
     *
     * @param   string $tag     the tag term you are searching posts for.
     * @param   array $options  additional parameters to refine your query
     * @return  Zend_Service_Technorati_TagResultSet
     * @throws  Zend_Service_Technorati_Exception
     *  @link    http://technorati.com/developers/api/tag.html Technorati API: Tag Query reference
     */
    public function tag($tag, $options = null)
    {
        static $defaultOptions = array( 'start'          => 1,
                                        'limit'          => 20,
                                        'format'         => 'xml',
                                        'excerptsize'    => 100,
                                        'topexcerptsize' => 150);

        $options['tag'] = $tag;

        $options = $this->_prepareOptions($options, $defaultOptions);
        $this->_validateTag($options);
        $response = $this->_makeRequest(self::API_PATH_TAG, $options);
        $dom = $this->_convertResponseAndCheckContent($response);

        /**
         * @see Zend_Service_Technorati_TagResultSet
         */
        #require_once 'Zend/Service/Technorati/TagResultSet.php';
        return new Zend_Service_Technorati_TagResultSet($dom, $options);
    }

    /**
     * TopTags provides daily counts of posts containing the queried keyword.
     *
     * Query options include:
     *
     * 'days'       => (int)
     *      optional - Used to specify the number of days in the past
     *      to request daily count data for.
     *      Can be any integer between 1 and 180, default is 180
     *
     * @param   string $q       the keyword query
     * @param   array $options  additional parameters to refine your query
     * @return  Zend_Service_Technorati_DailyCountsResultSet
     * @throws  Zend_Service_Technorati_Exception
     * @link    http://technorati.com/developers/api/dailycounts.html Technorati API: DailyCounts Query reference
     */
    public function dailyCounts($query, $options = null)
    {
        static $defaultOptions = array( 'days'      => 180,
                                        'format'    => 'xml'
                                        );

        $options['q'] = $query;

        $options = $this->_prepareOptions($options, $defaultOptions);
        $this->_validateDailyCounts($options);
        $response = $this->_makeRequest(self::API_PATH_DAILYCOUNTS, $options);
        $dom = $this->_convertResponseAndCheckContent($response);

        /**
         * @see Zend_Service_Technorati_DailyCountsResultSet
         */
        #require_once 'Zend/Service/Technorati/DailyCountsResultSet.php';
        return new Zend_Service_Technorati_DailyCountsResultSet($dom);
    }

    /**
     * TopTags provides information on top tags indexed by Technorati.
     *
     * Query options include:
     *
     * 'limit'      => (int)
     *      optional - adjust the size of your result from the default value of 20
     *      to between 1 and 100 results.
     * 'start'      => (int)
     *      optional - adjust the range of your result set.
     *      Set this number to larger than zero and you will receive
     *      the portion of Technorati's total result set ranging from start to start+limit.
     *      The default start value is 1.
     *
     * @param   array $options  additional parameters to refine your query
     * @return  Zend_Service_Technorati_TagsResultSet
     * @throws  Zend_Service_Technorati_Exception
     * @link    http://technorati.com/developers/api/toptags.html Technorati API: TopTags Query reference
     */
    public function topTags($options = null)
    {
        static $defaultOptions = array( 'start'     => 1,
                                        'limit'     => 20,
                                        'format'    => 'xml'
                                        );

        $options = $this->_prepareOptions($options, $defaultOptions);
        $this->_validateTopTags($options);
        $response = $this->_makeRequest(self::API_PATH_TOPTAGS, $options);
        $dom = $this->_convertResponseAndCheckContent($response);

        /**
         * @see Zend_Service_Technorati_TagsResultSet
         */
        #require_once 'Zend/Service/Technorati/TagsResultSet.php';
        return new Zend_Service_Technorati_TagsResultSet($dom);
    }

    /**
     * BlogInfo provides information on what blog, if any, is associated with a given URL.
     *
     * @param   string $url     the URL you are searching for. Prefixes http:// and www. are optional.
     *                          The URL must be recognized by Technorati as a blog.
     * @param   array $options  additional parameters to refine your query
     * @return  Zend_Service_Technorati_BlogInfoResult
     * @throws  Zend_Service_Technorati_Exception
     * @link    http://technorati.com/developers/api/bloginfo.html Technorati API: BlogInfo Query reference
     */
    public function blogInfo($url, $options = null)
    {
        static $defaultOptions = array( 'format'    => 'xml'
                                        );

        $options['url'] = $url;

        $options = $this->_prepareOptions($options, $defaultOptions);
        $this->_validateBlogInfo($options);
        $response = $this->_makeRequest(self::API_PATH_BLOGINFO, $options);
        $dom = $this->_convertResponseAndCheckContent($response);

        /**
         * @see Zend_Service_Technorati_BlogInfoResult
         */
        #require_once 'Zend/Service/Technorati/BlogInfoResult.php';
        return new Zend_Service_Technorati_BlogInfoResult($dom);
    }

    /**
     * BlogPostTags provides information on the top tags used by a specific blog.
     *
     * Query options include:
     *
     * 'limit'      => (int)
     *      optional - adjust the size of your result from the default value of 20
     *      to between 1 and 100 results.
     * 'start'      => (int)
     *      optional - adjust the range of your result set.
     *      Set this number to larger than zero and you will receive
     *      the portion of Technorati's total result set ranging from start to start+limit.
     *      The default start value is 1.
     *      Note. This property is not documented.
     *
     * @param   string $url     the URL you are searching for. Prefixes http:// and www. are optional.
     *                          The URL must be recognized by Technorati as a blog.
     * @param   array $options  additional parameters to refine your query
     * @return  Zend_Service_Technorati_TagsResultSet
     * @throws  Zend_Service_Technorati_Exception
     * @link    http://technorati.com/developers/api/blogposttags.html Technorati API: BlogPostTags Query reference
     */
    public function blogPostTags($url, $options = null)
    {
        static $defaultOptions = array( 'start'     => 1,
                                        'limit'     => 20,
                                        'format'    => 'xml'
                                        );

        $options['url'] = $url;

        $options = $this->_prepareOptions($options, $defaultOptions);
        $this->_validateBlogPostTags($options);
        $response = $this->_makeRequest(self::API_PATH_BLOGPOSTTAGS, $options);
        $dom = $this->_convertResponseAndCheckContent($response);

        /**
         * @see Zend_Service_Technorati_TagsResultSet
         */
        #require_once 'Zend/Service/Technorati/TagsResultSet.php';
        return new Zend_Service_Technorati_TagsResultSet($dom);
    }

    /**
     * GetInfo query tells you things that Technorati knows about a member.
     *
     * The returned info is broken up into two sections:
     * The first part describes some information that the user wants
     * to allow people to know about him- or herself.
     * The second part of the document is a listing of the weblogs
     * that the user has successfully claimed and the information
     * that Technorati knows about these weblogs.
     *
     * @param   string $username    the Technorati user name you are searching for
     * @param   array $options      additional parameters to refine your query
     * @return  Zend_Service_Technorati_GetInfoResult
     * @throws  Zend_Service_Technorati_Exception
     * @link    http://technorati.com/developers/api/getinfo.html Technorati API: GetInfo reference
     */
    public function getInfo($username, $options = null)
    {
        static $defaultOptions = array('format' => 'xml');

        $options['username'] = $username;

        $options = $this->_prepareOptions($options, $defaultOptions);
        $this->_validateGetInfo($options);
        $response = $this->_makeRequest(self::API_PATH_GETINFO, $options);
        $dom = $this->_convertResponseAndCheckContent($response);

        /**
         * @see Zend_Service_Technorati_GetInfoResult
         */
        #require_once 'Zend/Service/Technorati/GetInfoResult.php';
        return new Zend_Service_Technorati_GetInfoResult($dom);
    }

    /**
     * KeyInfo query provides information on daily usage of an API key.
     * Key Info Queries do not count against a key's daily query limit.
     *
     * A day is defined as 00:00-23:59 Pacific time.
     *
     * @return  Zend_Service_Technorati_KeyInfoResult
     * @throws  Zend_Service_Technorati_Exception
     * @link    http://developers.technorati.com/wiki/KeyInfo Technorati API: Key Info reference
     */
    public function keyInfo()
    {
        static $defaultOptions = array();

        $options = $this->_prepareOptions(array(), $defaultOptions);
        // you don't need to validate this request
        // because key is the only mandatory element
        // and it's already set in #_prepareOptions
        $response = $this->_makeRequest(self::API_PATH_KEYINFO, $options);
        $dom = $this->_convertResponseAndCheckContent($response);

        /**
         * @see Zend_Service_Technorati_KeyInfoResult
         */
        #require_once 'Zend/Service/Technorati/KeyInfoResult.php';
        return new Zend_Service_Technorati_KeyInfoResult($dom, $this->_apiKey);
    }


    /**
     * Returns Technorati API key.
     *
     * @return string   Technorati API key
     */
    public function getApiKey()
    {
        return $this->_apiKey;
    }

    /**
     * Returns a reference to the REST client object in use.
     *
     * If the reference hasn't being inizialized yet,
     * then a new Zend_Rest_Client instance is created.
     *
     * @return Zend_Rest_Client
     */
    public function getRestClient()
    {
        if ($this->_restClient === null) {
            /**
             * @see Zend_Rest_Client
             */
            #require_once 'Zend/Rest/Client.php';
            $this->_restClient = new Zend_Rest_Client(self::API_URI_BASE);
        }

        return $this->_restClient;
    }

    /**
     * Sets Technorati API key.
     *
     * Be aware that this function doesn't validate the key.
     * The key is validated as soon as the first API request is sent.
     * If the key is invalid, the API request method will throw
     * a Zend_Service_Technorati_Exception exception with Invalid Key message.
     *
     * @param   string $key     Technorati API Key
     * @return  void
     * @link    http://technorati.com/developers/apikey.html How to get your Technorati API Key
     */
    public function setApiKey($key)
    {
        $this->_apiKey = $key;
        return $this;
    }


    /**
     * Validates Cosmos query options.
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected function _validateCosmos(array $options)
    {
        static $validOptions = array('key', 'url',
            'type', 'limit', 'start', 'current', 'claim', 'highlight', 'format');

        // Validate keys in the $options array
        $this->_compareOptions($options, $validOptions);
        // Validate url (required)
        $this->_validateOptionUrl($options);
        // Validate limit (optional)
        $this->_validateOptionLimit($options);
        // Validate start (optional)
        $this->_validateOptionStart($options);
        // Validate format (optional)
        $this->_validateOptionFormat($options);
        // Validate type (optional)
        $this->_validateInArrayOption('type', $options, array('link', 'weblog'));
        // Validate claim (optional)
        $this->_validateOptionClaim($options);
        // Validate highlight (optional)
        $this->_validateIntegerOption('highlight', $options);
        // Validate current (optional)
        if (isset($options['current'])) {
            $tmp = (int) $options['current'];
            $options['current'] = $tmp ? 'yes' : 'no';
        }

    }

    /**
     * Validates Search query options.
     *
     * @param   array   $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected function _validateSearch(array $options)
    {
        static $validOptions = array('key', 'query',
            'language', 'authority', 'limit', 'start', 'claim', 'format');

        // Validate keys in the $options array
        $this->_compareOptions($options, $validOptions);
        // Validate query (required)
        $this->_validateMandatoryOption('query', $options);
        // Validate authority (optional)
        $this->_validateInArrayOption('authority', $options, array('n', 'a1', 'a4', 'a7'));
        // Validate limit (optional)
        $this->_validateOptionLimit($options);
        // Validate start (optional)
        $this->_validateOptionStart($options);
        // Validate claim (optional)
        $this->_validateOptionClaim($options);
        // Validate format (optional)
        $this->_validateOptionFormat($options);
    }

    /**
     * Validates Tag query options.
     *
     * @param   array   $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected function _validateTag(array $options)
    {
        static $validOptions = array('key', 'tag',
            'limit', 'start', 'excerptsize', 'topexcerptsize', 'format');

        // Validate keys in the $options array
        $this->_compareOptions($options, $validOptions);
        // Validate query (required)
        $this->_validateMandatoryOption('tag', $options);
        // Validate limit (optional)
        $this->_validateOptionLimit($options);
        // Validate start (optional)
        $this->_validateOptionStart($options);
        // Validate excerptsize (optional)
        $this->_validateIntegerOption('excerptsize', $options);
        // Validate excerptsize (optional)
        $this->_validateIntegerOption('topexcerptsize', $options);
        // Validate format (optional)
        $this->_validateOptionFormat($options);
    }


    /**
     * Validates DailyCounts query options.
     *
     * @param   array   $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected function _validateDailyCounts(array $options)
    {
        static $validOptions = array('key', 'q',
            'days', 'format');

        // Validate keys in the $options array
        $this->_compareOptions($options, $validOptions);
        // Validate q (required)
        $this->_validateMandatoryOption('q', $options);
        // Validate format (optional)
        $this->_validateOptionFormat($options);
        // Validate days (optional)
        if (isset($options['days'])) {
            $options['days'] = (int) $options['days'];
            if ($options['days'] < self::PARAM_DAYS_MIN_VALUE ||
                $options['days'] > self::PARAM_DAYS_MAX_VALUE) {
                /**
                 * @see Zend_Service_Technorati_Exception
                 */
                #require_once 'Zend/Service/Technorati/Exception.php';
                throw new Zend_Service_Technorati_Exception(
                            "Invalid value '" . $options['days'] . "' for 'days' option");
            }
        }
    }

    /**
     * Validates GetInfo query options.
     *
     * @param   array   $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected function _validateGetInfo(array $options)
    {
        static $validOptions = array('key', 'username',
            'format');

        // Validate keys in the $options array
        $this->_compareOptions($options, $validOptions);
        // Validate username (required)
        $this->_validateMandatoryOption('username', $options);
        // Validate format (optional)
        $this->_validateOptionFormat($options);
    }

    /**
     * Validates TopTags query options.
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected function _validateTopTags(array $options)
    {
        static $validOptions = array('key',
            'limit', 'start', 'format');

        // Validate keys in the $options array
        $this->_compareOptions($options, $validOptions);
        // Validate limit (optional)
        $this->_validateOptionLimit($options);
        // Validate start (optional)
        $this->_validateOptionStart($options);
        // Validate format (optional)
        $this->_validateOptionFormat($options);
    }

    /**
     * Validates BlogInfo query options.
     *
     * @param   array   $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected function _validateBlogInfo(array $options)
    {
        static $validOptions = array('key', 'url',
            'format');

        // Validate keys in the $options array
        $this->_compareOptions($options, $validOptions);
        // Validate url (required)
        $this->_validateOptionUrl($options);
        // Validate format (optional)
        $this->_validateOptionFormat($options);
    }

    /**
     * Validates TopTags query options.
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected function _validateBlogPostTags(array $options)
    {
        static $validOptions = array('key', 'url',
            'limit', 'start', 'format');

        // Validate keys in the $options array
        $this->_compareOptions($options, $validOptions);
        // Validate url (required)
        $this->_validateOptionUrl($options);
        // Validate limit (optional)
        $this->_validateOptionLimit($options);
        // Validate start (optional)
        $this->_validateOptionStart($options);
        // Validate format (optional)
        $this->_validateOptionFormat($options);
    }

    /**
     * Checks whether an option is in a given array.
     *
     * @param   string $name    option name
     * @param   array $options
     * @param   array $array    array of valid options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected function _validateInArrayOption($name, $options, array $array)
    {
        if (isset($options[$name]) && !in_array($options[$name], $array)) {
            /**
             * @see Zend_Service_Technorati_Exception
             */
            #require_once 'Zend/Service/Technorati/Exception.php';
            throw new Zend_Service_Technorati_Exception(
                        "Invalid value '{$options[$name]}' for '$name' option");
        }
    }

    /**
     * Checks whether mandatory $name option exists and it's valid.
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected function _validateMandatoryOption($name, $options)
    {
        if (!isset($options[$name]) || !trim($options[$name])) {
            /**
             * @see Zend_Service_Technorati_Exception
             */
            #require_once 'Zend/Service/Technorati/Exception.php';
            throw new Zend_Service_Technorati_Exception(
                        "Empty value for '$name' option");
        }
    }

    /**
     * Checks whether $name option is a valid integer and casts it.
     *
     * @param   array $options
     * @return  void
     * @access  protected
     */
    protected function _validateIntegerOption($name, $options)
    {
        if (isset($options[$name])) {
            $options[$name] = (int) $options[$name];
        }
    }

    /**
     * Makes and HTTP GET request to given $path with $options.
     * HTTP Response is first validated, then returned.
     *
     * @param   string $path
     * @param   array $options
     * @return  Zend_Http_Response
     * @throws  Zend_Service_Technorati_Exception on failure
     * @access  protected
     */
    protected function _makeRequest($path, $options = array())
    {
        $restClient = $this->getRestClient();
        $restClient->getHttpClient()->resetParameters();
        $response = $restClient->restGet($path, $options);
        self::_checkResponse($response);
        return $response;
    }

    /**
     * Checks whether 'claim' option value is valid.
     *
     * @param   array $options
     * @return  void
     * @access  protected
     */
    protected function _validateOptionClaim(array $options)
    {
        $this->_validateIntegerOption('claim', $options);
    }

    /**
     * Checks whether 'format' option value is valid.
     * Be aware that Zend_Service_Technorati supports only XML as format value.
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception if 'format' value != XML
     * @access  protected
     */
    protected function _validateOptionFormat(array $options)
    {
        if (isset($options['format']) && $options['format'] != 'xml') {
            /**
             * @see Zend_Service_Technorati_Exception
             */
            #require_once 'Zend/Service/Technorati/Exception.php';
            throw new Zend_Service_Technorati_Exception(
                        "Invalid value '" . $options['format'] . "' for 'format' option. " .
                        "Zend_Service_Technorati supports only 'xml'");
        }
    }

    /**
     * Checks whether 'limit' option value is valid.
     * Value must be an integer greater than PARAM_LIMIT_MIN_VALUE
     * and lower than PARAM_LIMIT_MAX_VALUE.
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception if 'limit' value is invalid
     * @access  protected
     */
    protected function _validateOptionLimit(array $options)
    {
        if (!isset($options['limit'])) return;

        $options['limit'] = (int) $options['limit'];
        if ($options['limit'] < self::PARAM_LIMIT_MIN_VALUE ||
            $options['limit'] > self::PARAM_LIMIT_MAX_VALUE) {
            /**
             * @see Zend_Service_Technorati_Exception
             */
            #require_once 'Zend/Service/Technorati/Exception.php';
            throw new Zend_Service_Technorati_Exception(
                        "Invalid value '" . $options['limit'] . "' for 'limit' option");
        }
    }

    /**
     * Checks whether 'start' option value is valid.
     * Value must be an integer greater than 0.
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception if 'start' value is invalid
     * @access  protected
     */
    protected function _validateOptionStart(array $options)
    {
        if (!isset($options['start'])) return;

        $options['start'] = (int) $options['start'];
        if ($options['start'] < self::PARAM_START_MIN_VALUE) {
            /**
             * @see Zend_Service_Technorati_Exception
             */
            #require_once 'Zend/Service/Technorati/Exception.php';
            throw new Zend_Service_Technorati_Exception(
                        "Invalid value '" . $options['start'] . "' for 'start' option");
        }
    }

    /**
     * Checks whether 'url' option value exists and is valid.
     * 'url' must be a valid HTTP(s) URL.
     *
     * @param   array $options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception if 'url' value is invalid
     * @access  protected
     * @todo    support for Zend_Uri_Http
     */
    protected function _validateOptionUrl(array $options)
    {
        $this->_validateMandatoryOption('url', $options);
    }

    /**
     * Checks XML response content for errors.
     *
     * @param   DomDocument $dom    the XML response as a DOM document
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @link    http://technorati.com/developers/api/error.html Technorati API: Error response
     * @access  protected
     */
    protected static function _checkErrors(DomDocument $dom)
    {
        $xpath = new DOMXPath($dom);

        $result = $xpath->query("/tapi/document/result/error");
        if ($result->length >= 1) {
            $error = $result->item(0)->nodeValue;
            /**
             * @see Zend_Service_Technorati_Exception
             */
            #require_once 'Zend/Service/Technorati/Exception.php';
            throw new Zend_Service_Technorati_Exception($error);
        }
    }

    /**
     * Converts $response body to a DOM object and checks it.
     *
     * @param   Zend_Http_Response $response
     * @return  DOMDocument
     * @throws  Zend_Service_Technorati_Exception if response content contains an error message
     * @access  protected
     */
    protected function _convertResponseAndCheckContent(Zend_Http_Response $response)
    {
        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());
        self::_checkErrors($dom);
        return $dom;
    }

    /**
     * Checks ReST response for errors.
     *
     * @param   Zend_Http_Response $response    the ReST response
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected static function _checkResponse(Zend_Http_Response $response)
    {
        if ($response->isError()) {
            /**
             * @see Zend_Service_Technorati_Exception
             */
            #require_once 'Zend/Service/Technorati/Exception.php';
            throw new Zend_Service_Technorati_Exception(sprintf(
                        'Invalid response status code (HTTP/%s %s %s)',
                        $response->getVersion(), $response->getStatus(), $response->getMessage()));
        }
    }

    /**
     * Checks whether user given options are valid.
     *
     * @param   array $options        user options
     * @param   array $validOptions   valid options
     * @return  void
     * @throws  Zend_Service_Technorati_Exception
     * @access  protected
     */
    protected function _compareOptions(array $options, array $validOptions)
    {
        $difference = array_diff(array_keys($options), $validOptions);
        if ($difference) {
            /**
             * @see Zend_Service_Technorati_Exception
             */
            #require_once 'Zend/Service/Technorati/Exception.php';
            throw new Zend_Service_Technorati_Exception(
                        "The following parameters are invalid: '" .
                        implode("', '", $difference) . "'");
        }
    }

    /**
     * Prepares options for the request
     *
     * @param   array $options        user options
     * @param   array $defaultOptions default options
     * @return  array Merged array of user and default/required options.
     * @access  protected
     */
    protected function _prepareOptions($options, array $defaultOptions)
    {
        $options = (array) $options; // force cast to convert null to array()
        $options['key'] = $this->_apiKey;
        $options = array_merge($defaultOptions, $options);
        return $options;
    }
}
