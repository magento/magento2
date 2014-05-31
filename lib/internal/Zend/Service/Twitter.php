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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Twitter.php 23312 2010-11-08 19:45:00Z matthew $
 */

/**
 * @see Zend_Rest_Client
 */
#require_once 'Zend/Rest/Client.php';

/**
 * @see Zend_Rest_Client_Result
 */
#require_once 'Zend/Rest/Client/Result.php';

/**
 * @see Zend_Oauth_Consumer
 */
#require_once 'Zend/Oauth/Consumer.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Twitter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Twitter extends Zend_Rest_Client
{

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
     * OAuth Endpoint
     */
    const OAUTH_BASE_URI = 'http://twitter.com/oauth';
    
    /**
     * @var Zend_Http_CookieJar
     */
    protected $_cookieJar;
    
    /**
     * Date format for 'since' strings
     *
     * @var string
     */
    protected $_dateFormat = 'D, d M Y H:i:s T';
    
    /**
     * Username
     *
     * @var string
     */
    protected $_username;
    
    /**
     * Current method type (for method proxying)
     *
     * @var string
     */
    protected $_methodType;
    
    /**
     * Zend_Oauth Consumer
     *
     * @var Zend_Oauth_Consumer
     */
    protected $_oauthConsumer = null;
    
    /**
     * Types of API methods
     *
     * @var array
     */
    protected $_methodTypes = array(
        'status',
        'user',
        'directMessage',
        'friendship',
        'account',
        'favorite',
        'block'
    );
    
    /**
     * Options passed to constructor
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Local HTTP Client cloned from statically set client
     *
     * @var Zend_Http_Client
     */
    protected $_localHttpClient = null;

    /**
     * Constructor
     *
     * @param  array $options Optional options array
     * @return void
     */
    public function __construct($options = null, Zend_Oauth_Consumer $consumer = null)
    {
        $this->setUri('http://api.twitter.com');
        if (!is_array($options)) $options = array();
        $options['siteUrl'] = self::OAUTH_BASE_URI;
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        $this->_options = $options;
        if (isset($options['username'])) {
            $this->setUsername($options['username']);
        }
        if (isset($options['accessToken'])
        && $options['accessToken'] instanceof Zend_Oauth_Token_Access) {
            $this->setLocalHttpClient($options['accessToken']->getHttpClient($options));
        } else {
            $this->setLocalHttpClient(clone self::getHttpClient());
            if ($consumer === null) {
                $this->_oauthConsumer = new Zend_Oauth_Consumer($options);
            } else {
                $this->_oauthConsumer = $consumer;
            }
        }
    }

    /**
     * Set local HTTP client as distinct from the static HTTP client
     * as inherited from Zend_Rest_Client.
     *
     * @param Zend_Http_Client $client
     * @return self
     */
    public function setLocalHttpClient(Zend_Http_Client $client)
    {
        $this->_localHttpClient = $client;
        $this->_localHttpClient->setHeaders('Accept-Charset', 'ISO-8859-1,utf-8');
        return $this;
    }
    
    /**
     * Get the local HTTP client as distinct from the static HTTP client
     * inherited from Zend_Rest_Client
     *
     * @return Zend_Http_Client
     */
    public function getLocalHttpClient()
    {
        return $this->_localHttpClient;
    }
    
    /**
     * Checks for an authorised state
     *
     * @return bool
     */
    public function isAuthorised()
    {
        if ($this->getLocalHttpClient() instanceof Zend_Oauth_Client) {
            return true;
        }
        return false;
    }

    /**
     * Retrieve username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }

    /**
     * Set username
     *
     * @param  string $value
     * @return Zend_Service_Twitter
     */
    public function setUsername($value)
    {
        $this->_username = $value;
        return $this;
    }

    /**
     * Proxy service methods
     *
     * @param  string $type
     * @return Zend_Service_Twitter
     * @throws Zend_Service_Twitter_Exception If method not in method types list
     */
    public function __get($type)
    {
        if (!in_array($type, $this->_methodTypes)) {
            include_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Invalid method type "' . $type . '"'
            );
        }
        $this->_methodType = $type;
        return $this;
    }

    /**
     * Method overloading
     *
     * @param  string $method
     * @param  array $params
     * @return mixed
     * @throws Zend_Service_Twitter_Exception if unable to find method
     */
    public function __call($method, $params)
    {
        if (method_exists($this->_oauthConsumer, $method)) {
            $return = call_user_func_array(array($this->_oauthConsumer, $method), $params);
            if ($return instanceof Zend_Oauth_Token_Access) {
                $this->setLocalHttpClient($return->getHttpClient($this->_options));
            }
            return $return;
        }
        if (empty($this->_methodType)) {
            include_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Invalid method "' . $method . '"'
            );
        }
        $test = $this->_methodType . ucfirst($method);
        if (!method_exists($this, $test)) {
            include_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Invalid method "' . $test . '"'
            );
        }

        return call_user_func_array(array($this, $test), $params);
    }

    /**
     * Initialize HTTP authentication
     *
     * @return void
     */
    protected function _init()
    {
        if (!$this->isAuthorised() && $this->getUsername() !== null) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Twitter session is unauthorised. You need to initialize '
                . 'Zend_Service_Twitter with an OAuth Access Token or use '
                . 'its OAuth functionality to obtain an Access Token before '
                . 'attempting any API actions that require authorisation'
            );
        }
        $client = $this->_localHttpClient;
        $client->resetParameters();
        if (null == $this->_cookieJar) {
            $client->setCookieJar();
            $this->_cookieJar = $client->getCookieJar();
        } else {
            $client->setCookieJar($this->_cookieJar);
        }
    }

    /**
     * Set date header
     *
     * @param  int|string $value
     * @deprecated Not supported by Twitter since April 08, 2009
     * @return void
     */
    protected function _setDate($value)
    {
        if (is_int($value)) {
            $date = date($this->_dateFormat, $value);
        } else {
            $date = date($this->_dateFormat, strtotime($value));
        }
        $this->_localHttpClient->setHeaders('If-Modified-Since', $date);
    }

    /**
     * Public Timeline status
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusPublicTimeline()
    {
        $this->_init();
        $path = '/1/statuses/public_timeline.xml';
        $response = $this->_get($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Friend Timeline Status
     *
     * $params may include one or more of the following keys
     * - id: ID of a friend whose timeline you wish to receive
     * - count: how many statuses to return
     * - since_id: return results only after the specific tweet
     * - page: return page X of results
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return void
     */
    public function statusFriendsTimeline(array $params = array())
    {
        $this->_init();
        $path = '/1/statuses/friends_timeline';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'count':
                    $count = (int) $value;
                    if (0 >= $count) {
                        $count = 1;
                    } elseif (200 < $count) {
                        $count = 200;
                    }
                    $_params['count'] = (int) $count;
                    break;
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                default:
                    break;
            }
        }
        $path .= '.xml';
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * User Timeline status
     *
     * $params may include one or more of the following keys
     * - id: ID of a friend whose timeline you wish to receive
     * - since_id: return results only after the tweet id specified
     * - page: return page X of results
     * - count: how many statuses to return
     * - max_id: returns only statuses with an ID less than or equal to the specified ID
     * - user_id: specifies the ID of the user for whom to return the user_timeline
     * - screen_name: specfies the screen name of the user for whom to return the user_timeline
     * - include_rts: whether or not to return retweets
     * - trim_user: whether to return just the user ID or a full user object; omit to return full object
     * - include_entities: whether or not to return entities nodes with tweet metadata
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusUserTimeline(array $params = array())
    {
        $this->_init();
        $path = '/1/statuses/user_timeline';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'id':
                    $path .= '/' . $value;
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                case 'count':
                    $count = (int) $value;
                    if (0 >= $count) {
                        $count = 1;
                    } elseif (200 < $count) {
                        $count = 200;
                    }
                    $_params['count'] = $count;
                    break;
                case 'user_id':
                    $_params['user_id'] = $this->_validInteger($value);
                    break;
                case 'screen_name':
                    $_params['screen_name'] = $this->_validateScreenName($value);
                    break;
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                case 'max_id':
                    $_params['max_id'] = $this->_validInteger($value);
                    break;
                case 'include_rts':
                case 'trim_user':
                case 'include_entities':
                    $_params[strtolower($key)] = $value ? '1' : '0';
                    break;
                default:
                    break;
            }
        }
        $path .= '.xml';
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Show a single status
     *
     * @param  int $id Id of status to show
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusShow($id)
    {
        $this->_init();
        $path = '/1/statuses/show/' . $this->_validInteger($id) . '.xml';
        $response = $this->_get($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Update user's current status
     *
     * @param  string $status
     * @param  int $in_reply_to_status_id
     * @return Zend_Rest_Client_Result
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @throws Zend_Service_Twitter_Exception if message is too short or too long
     */
    public function statusUpdate($status, $inReplyToStatusId = null)
    {
        $this->_init();
        $path = '/1/statuses/update.xml';
        $len = iconv_strlen(htmlspecialchars($status, ENT_QUOTES, 'UTF-8'), 'UTF-8');
        if ($len > self::STATUS_MAX_CHARACTERS) {
            include_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Status must be no more than '
                . self::STATUS_MAX_CHARACTERS
                . ' characters in length'
            );
        } elseif (0 == $len) {
            include_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Status must contain at least one character'
            );
        }
        $data = array('status' => $status);
        if (is_numeric($inReplyToStatusId) && !empty($inReplyToStatusId)) {
            $data['in_reply_to_status_id'] = $inReplyToStatusId;
        }
        $response = $this->_post($path, $data);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Get status replies
     *
     * $params may include one or more of the following keys
     * - since_id: return results only after the specified tweet id
     * - page: return page X of results
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusReplies(array $params = array())
    {
        $this->_init();
        $path = '/1/statuses/mentions.xml';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Destroy a status message
     *
     * @param  int $id ID of status to destroy
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusDestroy($id)
    {
        $this->_init();
        $path = '/1/statuses/destroy/' . $this->_validInteger($id) . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * User friends
     *
     * @param  int|string $id Id or username of user for whom to fetch friends
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function userFriends(array $params = array())
    {
        $this->_init();
        $path = '/1/statuses/friends';
        $_params = array();

        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'id':
                    $path .= '/' . $value;
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                default:
                    break;
            }
        }
        $path .= '.xml';

        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * User Followers
     *
     * @param  bool $lite If true, prevents inline inclusion of current status for followers; defaults to false
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function userFollowers($lite = false)
    {
        $this->_init();
        $path = '/1/statuses/followers.xml';
        if ($lite) {
            $this->lite = 'true';
        }
        $response = $this->_get($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Show extended information on a user
     *
     * @param  int|string $id User ID or name
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function userShow($id)
    {
        $this->_init();
        $path = '/1/users/show.xml';
        $response = $this->_get($path, array('id'=>$id));
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Retrieve direct messages for the current user
     *
     * $params may include one or more of the following keys
     * - since_id: return statuses only greater than the one specified
     * - page: return page X of results
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function directMessageMessages(array $params = array())
    {
        $this->_init();
        $path = '/1/direct_messages.xml';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Retrieve list of direct messages sent by current user
     *
     * $params may include one or more of the following keys
     * - since_id: return statuses only greater than the one specified
     * - page: return page X of results
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function directMessageSent(array $params = array())
    {
        $this->_init();
        $path = '/1/direct_messages/sent.xml';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'since_id':
                    $_params['since_id'] = $this->_validInteger($value);
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                default:
                    break;
            }
        }
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Send a direct message to a user
     *
     * @param  int|string $user User to whom to send message
     * @param  string $text Message to send to user
     * @return Zend_Rest_Client_Result
     * @throws Zend_Service_Twitter_Exception if message is too short or too long
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     */
    public function directMessageNew($user, $text)
    {
        $this->_init();
        $path = '/1/direct_messages/new.xml';
        $len = iconv_strlen($text, 'UTF-8');
        if (0 == $len) {
            throw new Zend_Service_Twitter_Exception(
                'Direct message must contain at least one character'
            );
        } elseif (140 < $len) {
            throw new Zend_Service_Twitter_Exception(
                'Direct message must contain no more than 140 characters'
            );
        }
        $data = array('user' => $user, 'text' => $text);
        $response = $this->_post($path, $data);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Destroy a direct message
     *
     * @param  int $id ID of message to destroy
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function directMessageDestroy($id)
    {
        $this->_init();
        $path = '/1/direct_messages/destroy/' . $this->_validInteger($id) . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Create friendship
     *
     * @param  int|string $id User ID or name of new friend
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function friendshipCreate($id)
    {
        $this->_init();
        $path = '/1/friendships/create/' . $id . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Destroy friendship
     *
     * @param  int|string $id User ID or name of friend to remove
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function friendshipDestroy($id)
    {
        $this->_init();
        $path = '/1/friendships/destroy/' . $id . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Friendship exists
     *
     * @param int|string $id User ID or name of friend to see if they are your friend
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_result
     */
    public function friendshipExists($id)
    {
        $this->_init();
        $path = '/1/friendships/exists.xml';
        $data = array('user_a' => $this->getUsername(), 'user_b' => $id);
        $response = $this->_get($path, $data);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Verify Account Credentials
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     *
     * @return Zend_Rest_Client_Result
     */
    public function accountVerifyCredentials()
    {
        $this->_init();
        $response = $this->_get('/1/account/verify_credentials.xml');
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * End current session
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return true
     */
    public function accountEndSession()
    {
        $this->_init();
        $this->_get('/1/account/end_session');
        return true;
    }

    /**
     * Returns the number of api requests you have left per hour.
     *
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function accountRateLimitStatus()
    {
        $this->_init();
        $response = $this->_get('/1/account/rate_limit_status.xml');
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Fetch favorites
     *
     * $params may contain one or more of the following:
     * - 'id': Id of a user for whom to fetch favorites
     * - 'page': Retrieve a different page of resuls
     *
     * @param  array $params
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function favoriteFavorites(array $params = array())
    {
        $this->_init();
        $path = '/1/favorites';
        $_params = array();
        foreach ($params as $key => $value) {
            switch (strtolower($key)) {
                case 'id':
                    $path .= '/' . $this->_validInteger($value);
                    break;
                case 'page':
                    $_params['page'] = (int) $value;
                    break;
                default:
                    break;
            }
        }
        $path .= '.xml';
        $response = $this->_get($path, $_params);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Mark a status as a favorite
     *
     * @param  int $id Status ID you want to mark as a favorite
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function favoriteCreate($id)
    {
        $this->_init();
        $path = '/1/favorites/create/' . $this->_validInteger($id) . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Remove a favorite
     *
     * @param  int $id Status ID you want to de-list as a favorite
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function favoriteDestroy($id)
    {
        $this->_init();
        $path = '/1/favorites/destroy/' . $this->_validInteger($id) . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Blocks the user specified in the ID parameter as the authenticating user.
     * Destroys a friendship to the blocked user if it exists.
     *
     * @param integer|string $id       The ID or screen name of a user to block.
     * @return Zend_Rest_Client_Result
     */
    public function blockCreate($id)
    {
        $this->_init();
        $path = '/1/blocks/create/' . $id . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Un-blocks the user specified in the ID parameter for the authenticating user
     *
     * @param integer|string $id       The ID or screen_name of the user to un-block.
     * @return Zend_Rest_Client_Result
     */
    public function blockDestroy($id)
    {
        $this->_init();
        $path = '/1/blocks/destroy/' . $id . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Returns if the authenticating user is blocking a target user.
     *
     * @param string|integer $id    The ID or screen_name of the potentially blocked user.
     * @param boolean $returnResult Instead of returning a boolean return the rest response from twitter
     * @return Boolean|Zend_Rest_Client_Result
     */
    public function blockExists($id, $returnResult = false)
    {
        $this->_init();
        $path = '/1/blocks/exists/' . $id . '.xml';
        $response = $this->_get($path);

        $cr = new Zend_Rest_Client_Result($response->getBody());

        if ($returnResult === true)
            return $cr;

        if (!empty($cr->request)) {
            return false;
        }

        return true;
    }

    /**
     * Returns an array of user objects that the authenticating user is blocking
     *
     * @param integer $page         Optional. Specifies the page number of the results beginning at 1. A single page contains 20 ids.
     * @param boolean $returnUserIds  Optional. Returns only the userid's instead of the whole user object
     * @return Zend_Rest_Client_Result
     */
    public function blockBlocking($page = 1, $returnUserIds = false)
    {
        $this->_init();
        $path = '/1/blocks/blocking';
        if ($returnUserIds === true) {
            $path .= '/ids';
        }
        $path .= '.xml';
        $response = $this->_get($path, array('page' => $page));
        return new Zend_Rest_Client_Result($response->getBody());
    }

    /**
     * Protected function to validate that the integer is valid or return a 0
     * @param $int
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return integer
     */
    protected function _validInteger($int)
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
     * @throws Zend_Service_Twitter_Exception
     * @return string
     */
    protected function _validateScreenName($name)
    {
        if (!preg_match('/^[a-zA-Z0-9_]{0,15}$/', $name)) {
            #require_once 'Zend/Service/Twitter/Exception.php';
            throw new Zend_Service_Twitter_Exception(
                'Screen name, "' . $name
                . '" should only contain alphanumeric characters and'
                . ' underscores, and not exceed 15 characters.');
        }
        return $name;
    }

    /**
     * Call a remote REST web service URI and return the Zend_Http_Response object
     *
     * @param  string $path            The path to append to the URI
     * @throws Zend_Rest_Client_Exception
     * @return void
     */
    protected function _prepare($path)
    {
        // Get the URI object and configure it
        if (!$this->_uri instanceof Zend_Uri_Http) {
            #require_once 'Zend/Rest/Client/Exception.php';
            throw new Zend_Rest_Client_Exception(
                'URI object must be set before performing call'
            );
        }

        $uri = $this->_uri->getUri();

        if ($path[0] != '/' && $uri[strlen($uri) - 1] != '/') {
            $path = '/' . $path;
        }

        $this->_uri->setPath($path);

        /**
         * Get the HTTP client and configure it for the endpoint URI.
         * Do this each time because the Zend_Http_Client instance is shared
         * among all Zend_Service_Abstract subclasses.
         */
        $this->_localHttpClient->resetParameters()->setUri((string) $this->_uri);
    }

    /**
     * Performs an HTTP GET request to the $path.
     *
     * @param string $path
     * @param array  $query Array of GET parameters
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
    protected function _get($path, array $query = null)
    {
        $this->_prepare($path);
        $this->_localHttpClient->setParameterGet($query);
        return $this->_localHttpClient->request(Zend_Http_Client::GET);
    }

    /**
     * Performs an HTTP POST request to $path.
     *
     * @param string $path
     * @param mixed $data Raw data to send
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
    protected function _post($path, $data = null)
    {
        $this->_prepare($path);
        return $this->_performPost(Zend_Http_Client::POST, $data);
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
    protected function _performPost($method, $data = null)
    {
        $client = $this->_localHttpClient;
        if (is_string($data)) {
            $client->setRawData($data);
        } elseif (is_array($data) || is_object($data)) {
            $client->setParameterPost((array) $data);
        }
        return $client->request($method);
    }

}
