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
 * @subpackage Twitter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Http_Client
 */
#require_once 'Zend/Http/Client.php';

/**
 * @see Zend_Http_CookieJar
 */
#require_once 'Zend/Http/CookieJar.php';

/**
 * @see Zend_Oauth_Consumer
 */
#require_once 'Zend/Oauth/Consumer.php';

/**
 * @see Zend_Oauth_Token_Access
 */
#require_once 'Zend/Oauth/Token/Access.php';

/**
 * @see Zend_Service_Twitter_Response
 */
#require_once 'Zend/Service/Twitter/Response.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Twitter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Twitter
{
    /**
     * Base URI for all API calls
     */
    const API_BASE_URI = 'https://api.twitter.com/1.1/';

    /**
     * OAuth Endpoint
     */
    const OAUTH_BASE_URI = 'https://api.twitter.com/oauth';

    /**
     * 246 is the current limit for a status message, 140 characters are displayed
     * initially, with the remainder linked from the web UI or client. The limit is
     * applied to a html encoded UTF-8 string (i.e. entities are counted in the limit
     * which may appear unusual but is a security measure).
     *
     * This should be reviewed in the future...
     */
    const STATUS_MAX_CHARACTERS = 246;

    /**
     * @var array
     */
    protected $cookieJar;

    /**
     * Date format for 'since' strings
     *
     * @var string
     */
    protected $dateFormat = 'D, d M Y H:i:s T';

    /**
     * @var Zend_Http_Client
     */
    protected $httpClient = null;

    /**
     * Current method type (for method proxying)
     *
     * @var string
     */
    protected $methodType;

    /**
     * Oauth Consumer
     *
     * @var Zend_Oauth_Consumer
     */
    protected $oauthConsumer = null;

    /**
     * Types of API methods
     *
     * @var array
     */
    protected $methodTypes = array(
        'account',
        'application',
        'blocks',
        'directmessages',
        'favorites',
        'friendships',
        'search',
        'statuses',
        'users',
    );

    /**
     * Options passed to constructor
     *
     * @var array
     */
    protected $options = array();

    /**
     * Username
     *
     * @var string
     */
    protected $username;

    /**
     * Constructor
     *
     * @param  null|array|Zend_Config $options
     * @param  null|Zend_Oauth_Consumer $consumer
     * @param  null|Zend_Http_Client $httpClient
     */
    public function __construct($options = null, Zend_Oauth_Consumer $consumer = null, Zend_Http_Client $httpClient = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (!is_array($options)) {
            $options = array();
        }

        $this->options = $options;

        if (isset($options['username'])) {
            $this->setUsername($options['username']);
        }

        $accessToken = false;
        if (isset($options['accessToken'])) {
            $accessToken = $options['accessToken'];
        } elseif (isset($options['access_token'])) {
            $accessToken = $options['access_token'];
        }

        $oauthOptions = array();
        if (isset($options['oauthOptions'])) {
            $oauthOptions = $options['oauthOptions'];
        } elseif (isset($options['oauth_options'])) {
            $oauthOptions = $options['oauth_options'];
        }
        $oauthOptions['siteUrl'] = self::OAUTH_BASE_URI;

        $httpClientOptions = array();
        if (isset($options['httpClientOptions'])) {
            $httpClientOptions = $options['httpClientOptions'];
        } elseif (isset($options['http_client_options'])) {
            $httpClientOptions = $options['http_client_options'];
        }

        // If we have an OAuth access token, use the HTTP client it provides
        if ($accessToken && is_array($accessToken)
            && (isset($accessToken['token']) && isset($accessToken['secret']))
        ) {
            $token = new Zend_Oauth_Token_Access();
            $token->setToken($accessToken['token']);
            $token->setTokenSecret($accessToken['secret']);
            $accessToken = $token;
        }
        if ($accessToken && $accessToken instanceof Zend_Oauth_Token_Access) {
            $oauthOptions['token'] = $accessToken;
            $this->setHttpClient($accessToken->getHttpClient($oauthOptions, self::OAUTH_BASE_URI, $httpClientOptions));
            return;
        }

        // See if we were passed an http client
        if (isset($options['httpClient']) && null === $httpClient) {
            $httpClient = $options['httpClient'];
        } elseif (isset($options['http_client']) && null === $httpClient) {
            $httpClient = $options['http_client'];
        }
        if ($httpClient instanceof Zend_Http_Client) {
            $this->httpClient = $httpClient;
        } else {
            $this->setHttpClient(new Zend_Http_Client(null, $httpClientOptions));
        }

        // Set the OAuth consumer
        if ($consumer === null) {
            $consumer = new Zend_Oauth_Consumer($oauthOptions);
        }
        $this->oauthConsumer = $consumer;
    }

    /**
     * Proxy service methods
     *
     * @param  string $type
     * @return Twitter
     * @throws Exception\DomainException If method not in method types list
     */
    public function __get($type)
    {
        $type = strtolower($type);
        $type = str_replace('_', '', $type);
        if (!in_array($type, $this->methodTypes)) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Invalid method type "' . $type . '"'
            );
        }
        $this->methodType = $type;
        return $this;
    }

    /**
     * Method overloading
     *
     * @param  string $method
     * @param  array $params
     * @return mixed
     * @throws Exception\BadMethodCallException if unable to find method
     */
    public function __call($method, $params)
    {
        if (method_exists($this->oauthConsumer, $method)) {
            $return = call_user_func_array(array($this->oauthConsumer, $method), $params);
            if ($return instanceof Zend_Oauth_Token_Access) {
                $this->setHttpClient($return->getHttpClient($this->options));
            }
            return $return;
        }
        if (empty($this->methodType)) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Invalid method "' . $method . '"'
            );
        }

        $test = str_replace('_', '', strtolower($method));
        $test = $this->methodType . $test;
        if (!method_exists($this, $test)) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Invalid method "' . $test . '"'
            );
        }

        return call_user_func_array(array($this, $test), $params);
    }

    /**
     * Set HTTP client
     *
     * @param Zend_Http_Client $client
     * @return self
     */
    public function setHttpClient(Zend_Http_Client $client)
    {
        $this->httpClient = $client;
        $this->httpClient->setHeaders(array('Accept-Charset' => 'ISO-8859-1,utf-8'));
        return $this;
    }

    /**
     * Get the HTTP client
     *
     * Lazy loads one if none present
     *
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if (null === $this->httpClient) {
            $this->setHttpClient(new Zend_Http_Client());
        }
        return $this->httpClient;
    }

    /**
     * Retrieve username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param  string $value
     * @return self
     */
    public function setUsername($value)
    {
        $this->username = $value;
        return $this;
    }

    /**
     * Checks for an authorised state
     *
     * @return bool
     */
    public function isAuthorised()
    {
        if ($this->getHttpClient() instanceof Zend_Oauth_Client) {
            return true;
        }
        return false;
    }

    /**
     * Verify Account Credentials
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function accountVerifyCredentials()
    {
        $this->init();
        $response = $this->get('account/verify_credentials');
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Returns the number of api requests you have left per hour.
     *
     * @todo   Have a separate payload object to represent rate limits
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function applicationRateLimitStatus()
    {
        $this->init();
        $response = $this->get('application/rate_limit_status');
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Blocks the user specified in the ID parameter as the authenticating user.
     * Destroys a friendship to the blocked user if it exists.
     *
     * @param  integer|string $id       The ID or screen name of a user to block.
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function blocksCreate($id)
    {
        $this->init();
        $path     = 'blocks/create';
        $params   = $this->createUserParameter($id, array());
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Un-blocks the user specified in the ID parameter for the authenticating user
     *
     * @param  integer|string $id       The ID or screen_name of the user to un-block.
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function blocksDestroy($id)
    {
        $this->init();
        $path   = 'blocks/destroy';
        $params = $this->createUserParameter($id, array());
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Returns an array of user ids that the authenticating user is blocking
     *
     * @param  integer $cursor  Optional. Specifies the cursor position at which to begin listing ids; defaults to first "page" of results.
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function blocksIds($cursor = -1)
    {
        $this->init();
        $path = 'blocks/ids';
        $response = $this->get($path, array('cursor' => $cursor));
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Returns an array of user objects that the authenticating user is blocking
     *
     * @param  integer $cursor  Optional. Specifies the cursor position at which to begin listing ids; defaults to first "page" of results.
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function blocksList($cursor = -1)
    {
        $this->init();
        $path = 'blocks/list';
        $response = $this->get($path, array('cursor' => $cursor));
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Destroy a direct message
     *
     * @param  int $id ID of message to destroy
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function directMessagesDestroy($id)
    {
        $this->init();
        $path     = 'direct_messages/destroy';
        $params   = array('id' => $this->validInteger($id));
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Retrieve direct messages for the current user
     *
     * $options may include one or more of the following keys
     * - count: return page X of results
     * - since_id: return statuses only greater than the one specified
     * - max_id: return statuses with an ID less than (older than) or equal to that specified
     * - include_entities: setting to false will disable embedded entities
     * - skip_status:setting to true, "t", or 1 will omit the status in returned users
     *
     * @param  array $options
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function directMessagesMessages(array $options = array())
    {
        $this->init();
        $path   = 'direct_messages';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                case 'skip_status':
                    $params['skip_status'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Send a direct message to a user
     *
     * @param  int|string $user User to whom to send message
     * @param  string $text Message to send to user
     * @throws Exception\InvalidArgumentException if message is empty
     * @throws Exception\OutOfRangeException if message is too long
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function directMessagesNew($user, $text)
    {
        $this->init();
        $path = 'direct_messages/new';

        $len = iconv_strlen($text, 'UTF-8');
        if (0 == $len) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Direct message must contain at least one character'
            );
        } elseif (140 < $len) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Direct message must contain no more than 140 characters'
            );
        }

        $params         = $this->createUserParameter($user, array());
        $params['text'] = $text;
        $response       = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Retrieve list of direct messages sent by current user
     *
     * $options may include one or more of the following keys
     * - count: return page X of results
     * - page: return starting at page
     * - since_id: return statuses only greater than the one specified
     * - max_id: return statuses with an ID less than (older than) or equal to that specified
     * - include_entities: setting to false will disable embedded entities
     *
     * @param  array $options
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function directMessagesSent(array $options = array())
    {
        $this->init();
        $path   = 'direct_messages/sent';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'page':
                    $params['page'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Mark a status as a favorite
     *
     * @param  int $id Status ID you want to mark as a favorite
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function favoritesCreate($id)
    {
        $this->init();
        $path     = 'favorites/create';
        $params   = array('id' => $this->validInteger($id));
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Remove a favorite
     *
     * @param  int $id Status ID you want to de-list as a favorite
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function favoritesDestroy($id)
    {
        $this->init();
        $path     = 'favorites/destroy';
        $params   = array('id' => $this->validInteger($id));
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Fetch favorites
     *
     * $options may contain one or more of the following:
     * - user_id: Id of a user for whom to fetch favorites
     * - screen_name: Screen name of a user for whom to fetch favorites
     * - count: number of tweets to attempt to retrieve, up to 200
     * - since_id: return results only after the specified tweet id
     * - max_id: return results with an ID less than (older than) or equal to the specified ID
     * - include_entities: when set to false, entities member will be omitted
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function favoritesList(array $options = array())
    {
        $this->init();
        $path = 'favorites/list';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'user_id':
                    $params['user_id'] = $this->validInteger($value);
                    break;
                case 'screen_name':
                    $params['screen_name'] = $value;
                    break;
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Create friendship
     *
     * @param  int|string $id User ID or name of new friend
     * @param  array $params Additional parameters to pass
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function friendshipsCreate($id, array $params = array())
    {
        $this->init();
        $path    = 'friendships/create';
        $params  = $this->createUserParameter($id, $params);
        $allowed = array(
            'user_id'     => null,
            'screen_name' => null,
            'follow'      => null,
        );
        $params = array_intersect_key($params, $allowed);
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Destroy friendship
     *
     * @param  int|string $id User ID or name of friend to remove
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function friendshipsDestroy($id)
    {
        $this->init();
        $path     = 'friendships/destroy';
        $params   = $this->createUserParameter($id, array());
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Search tweets
     *
     * $options may include any of the following:
     * - geocode: a string of the form "latitude, longitude, radius"
     * - lang: restrict tweets to the two-letter language code
     * - locale: query is in the given two-letter language code
     * - result_type: what type of results to receive: mixed, recent, or popular
     * - count: number of tweets to return per page; up to 100
     * - until: return tweets generated before the given date
     * - since_id: return resutls with an ID greater than (more recent than) the given ID
     * - max_id: return results with an ID less than (older than) the given ID
     * - include_entities: whether or not to include embedded entities
     *
     * @param  string $query
     * @param  array $options
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function searchTweets($query, array $options = array())
    {
        $this->init();
        $path = 'search/tweets';

        $len = iconv_strlen($query, 'UTF-8');
        if (0 == $len) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Query must contain at least one character'
            );
        }

        $params = array('q' => $query);
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'geocode':
                    if (!substr_count($value, ',') !== 2) {
                        #require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            '"geocode" must be of the format "latitude,longitude,radius"'
                        );
                    }
                    list($latitude, $longitude, $radius) = explode(',', $value);
                    $radius = trim($radius);
                    if (!preg_match('/^\d+(mi|km)$/', $radius)) {
                        #require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'Radius segment of "geocode" must be of the format "[unit](mi|km)"'
                        );
                    }
                    $latitude  = (float) $latitude;
                    $longitude = (float) $longitude;
                    $params['geocode'] = $latitude . ',' . $longitude . ',' . $radius;
                    break;
                case 'lang':
                    if (strlen($value) > 2) {
                        #require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'Query language must be a 2 character string'
                        );
                    }
                    $params['lang'] = strtolower($value);
                    break;
                case 'locale':
                    if (strlen($value) > 2) {
                        #require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'Query locale must be a 2 character string'
                        );
                    }
                    $params['locale'] = strtolower($value);
                    break;
                case 'result_type':
                    $value = strtolower($value);
                    if (!in_array($value, array('mixed', 'recent', 'popular'))) {
                        #require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'result_type must be one of "mixed", "recent", or "popular"'
                        );
                    }
                    $params['result_type'] = $value;
                    break;
                case 'count':
                    $value = (int) $value;
                    if (1 > $value || 100 < $value) {
                        #require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'count must be between 1 and 100'
                        );
                    }
                    $params['count'] = $value;
                    break;
                case 'until':
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                        #require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            '"until" must be a date in the format YYYY-MM-DD'
                        );
                    }
                    $params['until'] = $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Destroy a status message
     *
     * @param  int $id ID of status to destroy
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesDestroy($id)
    {
        $this->init();
        $path = 'statuses/destroy/' . $this->validInteger($id);
        $response = $this->post($path);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Friend Timeline Status
     *
     * $options may include one or more of the following keys
     * - count: number of tweets to attempt to retrieve, up to 200
     * - since_id: return results only after the specified tweet id
     * - max_id: return results with an ID less than (older than) or equal to the specified ID
     * - trim_user: when set to true, "t", or 1, user object in tweets will include only author's ID.
     * - contributor_details: when set to true, includes screen_name of each contributor
     * - include_entities: when set to false, entities member will be omitted
     * - exclude_replies: when set to true, will strip replies appearing in the timeline
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesHomeTimeline(array $options = array())
    {
        $this->init();
        $path = 'statuses/home_timeline';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'trim_user':
                    if (in_array($value, array(true, 'true', 't', 1, '1'))) {
                        $value = true;
                    } else {
                        $value = false;
                    }
                    $params['trim_user'] = $value;
                    break;
                case 'contributor_details:':
                    $params['contributor_details:'] = (bool) $value;
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                case 'exclude_replies':
                    $params['exclude_replies'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Get status replies
     *
     * $options may include one or more of the following keys
     * - count: number of tweets to attempt to retrieve, up to 200
     * - since_id: return results only after the specified tweet id
     * - max_id: return results with an ID less than (older than) or equal to the specified ID
     * - trim_user: when set to true, "t", or 1, user object in tweets will include only author's ID.
     * - contributor_details: when set to true, includes screen_name of each contributor
     * - include_entities: when set to false, entities member will be omitted
     *
     * @param  array $options
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesMentionsTimeline(array $options = array())
    {
        $this->init();
        $path   = 'statuses/mentions_timeline';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'trim_user':
                    if (in_array($value, array(true, 'true', 't', 1, '1'))) {
                        $value = true;
                    } else {
                        $value = false;
                    }
                    $params['trim_user'] = $value;
                    break;
                case 'contributor_details:':
                    $params['contributor_details:'] = (bool) $value;
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Public Timeline status
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesSample()
    {
        $this->init();
        $path = 'statuses/sample';
        $response = $this->get($path);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Show a single status
     *
     * @param  int $id Id of status to show
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesShow($id)
    {
        $this->init();
        $path = 'statuses/show/' . $this->validInteger($id);
        $response = $this->get($path);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Update user's current status
     *
     * @todo   Support additional parameters supported by statuses/update endpoint
     * @param  string $status
     * @param  null|int $inReplyToStatusId
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\OutOfRangeException if message is too long
     * @throws Exception\InvalidArgumentException if message is empty
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesUpdate($status, $inReplyToStatusId = null)
    {
        $this->init();
        $path = 'statuses/update';
        $len = iconv_strlen(htmlspecialchars($status, ENT_QUOTES, 'UTF-8'), 'UTF-8');
        if ($len > self::STATUS_MAX_CHARACTERS) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Status must be no more than '
                . self::STATUS_MAX_CHARACTERS
                . ' characters in length'
            );
        } elseif (0 == $len) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Status must contain at least one character'
            );
        }

        $params = array('status' => $status);
        $inReplyToStatusId = $this->validInteger($inReplyToStatusId);
        if ($inReplyToStatusId) {
            $params['in_reply_to_status_id'] = $inReplyToStatusId;
        }
        $response = $this->post($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * User Timeline status
     *
     * $options may include one or more of the following keys
     * - user_id: Id of a user for whom to fetch favorites
     * - screen_name: Screen name of a user for whom to fetch favorites
     * - count: number of tweets to attempt to retrieve, up to 200
     * - since_id: return results only after the specified tweet id
     * - max_id: return results with an ID less than (older than) or equal to the specified ID
     * - trim_user: when set to true, "t", or 1, user object in tweets will include only author's ID.
     * - exclude_replies: when set to true, will strip replies appearing in the timeline
     * - contributor_details: when set to true, includes screen_name of each contributor
     * - include_rts: when set to false, will strip native retweets
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function statusesUserTimeline(array $options = array())
    {
        $this->init();
        $path = 'statuses/user_timeline';
        $params = array();
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'user_id':
                    $params['user_id'] = $this->validInteger($value);
                    break;
                case 'screen_name':
                    $params['screen_name'] = $this->validateScreenName($value);
                    break;
                case 'count':
                    $params['count'] = (int) $value;
                    break;
                case 'since_id':
                    $params['since_id'] = $this->validInteger($value);
                    break;
                case 'max_id':
                    $params['max_id'] = $this->validInteger($value);
                    break;
                case 'trim_user':
                    if (in_array($value, array(true, 'true', 't', 1, '1'))) {
                        $value = true;
                    } else {
                        $value = false;
                    }
                    $params['trim_user'] = $value;
                    break;
                case 'contributor_details:':
                    $params['contributor_details:'] = (bool) $value;
                    break;
                case 'exclude_replies':
                    $params['exclude_replies'] = (bool) $value;
                    break;
                case 'include_rts':
                    $params['include_rts'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Search users
     *
     * $options may include any of the following:
     * - page: the page of results to retrieve
     * - count: the number of users to retrieve per page; max is 20
     * - include_entities: if set to boolean true, include embedded entities
     *
     * @param  string $query
     * @param  array $options
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function usersSearch($query, array $options = array())
    {
        $this->init();
        $path = 'users/search';

        $len = iconv_strlen($query, 'UTF-8');
        if (0 == $len) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Query must contain at least one character'
            );
        }

        $params = array('q' => $query);
        foreach ($options as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $value = (int) $value;
                    if (1 > $value || 20 < $value) {
                        #require_once 'Zend/Service/Twitter/Exception.php';
                        throw new Zend_Service_Twitter_Exception(
                            'count must be between 1 and 20'
                        );
                    }
                    $params['count'] = $value;
                    break;
                case 'page':
                    $params['page'] = (int) $value;
                    break;
                case 'include_entities':
                    $params['include_entities'] = (bool) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }


    /**
     * Show extended information on a user
     *
     * @param  int|string $id User ID or name
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Exception\DomainException if unable to decode JSON payload
     * @return Zend_Service_Twitter_Response
     */
    public function usersShow($id)
    {
        $this->init();
        $path     = 'users/show';
        $params   = $this->createUserParameter($id, array());
        $response = $this->get($path, $params);
        return new Zend_Service_Twitter_Response($response);
    }

    /**
     * Initialize HTTP authentication
     *
     * @return void
     * @throws Exception\DomainException if unauthorised
     */
    protected function init()
    {
        if (!$this->isAuthorised() && $this->getUsername() !== null) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Twitter session is unauthorised. You need to initialize '
                . __CLASS__ . ' with an OAuth Access Token or use '
                . 'its OAuth functionality to obtain an Access Token before '
                . 'attempting any API actions that require authorisation'
            );
        }
        $client = $this->getHttpClient();
        $client->resetParameters();
        if (null === $this->cookieJar) {
            $cookieJar = $client->getCookieJar();
            if (null === $cookieJar) {
                $cookieJar = new Zend_Http_CookieJar();
            }
            $this->cookieJar = $cookieJar;
            $this->cookieJar->reset();
        } else {
            $client->setCookieJar($this->cookieJar);
        }
    }

    /**
     * Protected function to validate that the integer is valid or return a 0
     *
     * @param  $int
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return integer
     */
    protected function validInteger($int)
    {
        if (preg_match("/(\d+)/", $int)) {
            return $int;
        }
        return 0;
    }

    /**
     * Validate a screen name using Twitter rules
     *
     * @param string $name
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    protected function validateScreenName($name)
    {
        if (!preg_match('/^[a-zA-Z0-9_]{0,20}$/', $name)) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Screen name, "' . $name
                . '" should only contain alphanumeric characters and'
                . ' underscores, and not exceed 15 characters.');
        }
        return $name;
    }

    /**
     * Call a remote REST web service URI
     *
     * @param  string $path The path to append to the URI
     * @param  Zend_Http_Client $client
     * @throws Zend_Http_Client_Exception
     * @return void
     */
    protected function prepare($path, Zend_Http_Client $client)
    {
        $client->setUri(self::API_BASE_URI . $path . '.json');

        /**
         * Do this each time to ensure oauth calls do not inject new params
         */
        $client->resetParameters();
    }

    /**
     * Performs an HTTP GET request to the $path.
     *
     * @param string $path
     * @param array  $query Array of GET parameters
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
    protected function get($path, array $query = array())
    {
        $client = $this->getHttpClient();
        $this->prepare($path, $client);
        $client->setParameterGet($query);
        $response = $client->request(Zend_Http_Client::GET);
        return $response;
    }

    /**
     * Performs an HTTP POST request to $path.
     *
     * @param string $path
     * @param mixed $data Raw data to send
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
    protected function post($path, $data = null)
    {
        $client = $this->getHttpClient();
        $this->prepare($path, $client);
        $response = $this->performPost(Zend_Http_Client::POST, $data, $client);
        return $response;
    }

    /**
     * Perform a POST or PUT
     *
     * Performs a POST or PUT request. Any data provided is set in the HTTP
     * client. String data is pushed in as raw POST data; array or object data
     * is pushed in as POST parameters.
     *
     * @param mixed $method
     * @param mixed $data
     * @return Zend_Http_Response
     */
    protected function performPost($method, $data, Zend_Http_Client $client)
    {
        if (is_string($data)) {
            $client->setRawData($data);
        } elseif (is_array($data) || is_object($data)) {
            $client->setParameterPost((array) $data);
        }
        return $client->request($method);
    }

    /**
     * Create a parameter representing the user
     *
     * Determines if $id is an integer, and, if so, sets the "user_id" parameter.
     * If not, assumes the $id is the "screen_name".
     *
     * @param  int|string $id
     * @param  array $params
     * @return array
     */
    protected function createUserParameter($id, array $params)
    {
        if ($this->validInteger($id)) {
            $params['user_id'] = $id;
            return $params;
        }

        $params['screen_name'] = $this->validateScreenName($id);
        return $params;
    }
}
