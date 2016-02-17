<?php

namespace OAuth\OAuth2\Service;

use OAuth\Common\Exception\Exception;
use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Http\Exception\TokenResponseException;
use OAuth\Common\Http\Uri\Uri;
use OAuth\Common\Consumer\CredentialsInterface;
use OAuth\Common\Http\Client\ClientInterface;
use OAuth\Common\Storage\TokenStorageInterface;
use OAuth\Common\Http\Uri\UriInterface;

class Facebook extends AbstractService
{
    /**
     * Facebook www url - used to build dialog urls
     */
    const WWW_URL = 'https://www.facebook.com/';

    /**
     * Defined scopes
     *
     * If you don't think this is scary you should not be allowed on the web at all
     *
     * @link https://developers.facebook.com/docs/reference/login/
     * @link https://developers.facebook.com/tools/explorer For a list of permissions use 'Get Access Token'
     */
    // Default scope
    const SCOPE_PUBLIC_PROFILE                = 'public_profile';
    // Email scopes
    const SCOPE_EMAIL                         = 'email';
    // Extended permissions
    const SCOPE_READ_FRIENDLIST               = 'read_friendlists';
    const SCOPE_READ_INSIGHTS                 = 'read_insights';
    const SCOPE_READ_MAILBOX                  = 'read_mailbox';
    const SCOPE_READ_PAGE_MAILBOXES           = 'read_page_mailboxes';
    const SCOPE_READ_REQUESTS                 = 'read_requests';
    const SCOPE_READ_STREAM                   = 'read_stream';
    const SCOPE_VIDEO_UPLOAD                  = 'video_upload';
    const SCOPE_XMPP_LOGIN                    = 'xmpp_login';
    const SCOPE_USER_ONLINE_PRESENCE          = 'user_online_presence';
    const SCOPE_FRIENDS_ONLINE_PRESENCE       = 'friends_online_presence';
    const SCOPE_ADS_MANAGEMENT                = 'ads_management';
    const SCOPE_ADS_READ                      = 'ads_read';
    const SCOPE_CREATE_EVENT                  = 'create_event';
    const SCOPE_CREATE_NOTE                   = 'create_note';
    const SCOPE_EXPORT_STREAM                 = 'export_stream';
    const SCOPE_MANAGE_FRIENDLIST             = 'manage_friendlists';
    const SCOPE_MANAGE_NOTIFICATIONS          = 'manage_notifications';
    const SCOPE_PHOTO_UPLOAD                  = 'photo_upload';
    const SCOPE_PUBLISH_ACTIONS               = 'publish_actions';
    const SCOPE_PUBLISH_CHECKINS              = 'publish_checkins';
    const SCOPE_PUBLISH_STREAM                = 'publish_stream';
    const SCOPE_RSVP_EVENT                    = 'rsvp_event';
    const SCOPE_SHARE_ITEM                    = 'share_item';
    const SCOPE_SMS                           = 'sms';
    const SCOPE_STATUS_UPDATE                 = 'status_update';
    // Extended Profile Properties
    const SCOPE_USER_POSTS                    = 'user_posts';
    const SCOPE_USER_FRIENDS                  = 'user_friends';
    const SCOPE_USER_ABOUT                    = 'user_about_me';
    const SCOPE_USER_TAGGED_PLACES            = 'user_tagged_places';
    const SCOPE_FRIENDS_ABOUT                 = 'friends_about_me';
    const SCOPE_USER_ACTIVITIES               = 'user_activities';
    const SCOPE_FRIENDS_ACTIVITIES            = 'friends_activities';
    const SCOPE_USER_BIRTHDAY                 = 'user_birthday';
    const SCOPE_FRIENDS_BIRTHDAY              = 'friends_birthday';
    const SCOPE_USER_CHECKINS                 = 'user_checkins';
    const SCOPE_FRIENDS_CHECKINS              = 'friends_checkins';
    const SCOPE_USER_EDUCATION                = 'user_education_history';
    const SCOPE_FRIENDS_EDUCATION             = 'friends_education_history';
    const SCOPE_USER_EVENTS                   = 'user_events';
    const SCOPE_FRIENDS_EVENTS                = 'friends_events';
    const SCOPE_USER_GROUPS                   = 'user_groups';
    const SCOPE_USER_MANAGED_GROUPS           = 'user_managed_groups';
    const SCOPE_FRIENDS_GROUPS                = 'friends_groups';
    const SCOPE_USER_HOMETOWN                 = 'user_hometown';
    const SCOPE_FRIENDS_HOMETOWN              = 'friends_hometown';
    const SCOPE_USER_INTERESTS                = 'user_interests';
    const SCOPE_FRIEND_INTERESTS              = 'friends_interests';
    const SCOPE_USER_LIKES                    = 'user_likes';
    const SCOPE_FRIENDS_LIKES                 = 'friends_likes';
    const SCOPE_USER_LOCATION                 = 'user_location';
    const SCOPE_FRIENDS_LOCATION              = 'friends_location';
    const SCOPE_USER_NOTES                    = 'user_notes';
    const SCOPE_FRIENDS_NOTES                 = 'friends_notes';
    const SCOPE_USER_PHOTOS                   = 'user_photos';
    const SCOPE_USER_PHOTO_VIDEO_TAGS         = 'user_photo_video_tags';
    const SCOPE_FRIENDS_PHOTOS                = 'friends_photos';
    const SCOPE_FRIENDS_PHOTO_VIDEO_TAGS      = 'friends_photo_video_tags';
    const SCOPE_USER_QUESTIONS                = 'user_questions';
    const SCOPE_FRIENDS_QUESTIONS             = 'friends_questions';
    const SCOPE_USER_RELATIONSHIPS            = 'user_relationships';
    const SCOPE_FRIENDS_RELATIONSHIPS         = 'friends_relationships';
    const SCOPE_USER_RELATIONSHIPS_DETAILS    = 'user_relationship_details';
    const SCOPE_FRIENDS_RELATIONSHIPS_DETAILS = 'friends_relationship_details';
    const SCOPE_USER_RELIGION                 = 'user_religion_politics';
    const SCOPE_FRIENDS_RELIGION              = 'friends_religion_politics';
    const SCOPE_USER_STATUS                   = 'user_status';
    const SCOPE_FRIENDS_STATUS                = 'friends_status';
    const SCOPE_USER_SUBSCRIPTIONS            = 'user_subscriptions';
    const SCOPE_FRIENDS_SUBSCRIPTIONS         = 'friends_subscriptions';
    const SCOPE_USER_VIDEOS                   = 'user_videos';
    const SCOPE_FRIENDS_VIDEOS                = 'friends_videos';
    const SCOPE_USER_WEBSITE                  = 'user_website';
    const SCOPE_FRIENDS_WEBSITE               = 'friends_website';
    const SCOPE_USER_WORK                     = 'user_work_history';
    const SCOPE_FRIENDS_WORK                  = 'friends_work_history';
    // Open Graph Permissions
    const SCOPE_USER_MUSIC                    = 'user_actions.music';
    const SCOPE_FRIENDS_MUSIC                 = 'friends_actions.music';
    const SCOPE_USER_NEWS                     = 'user_actions.news';
    const SCOPE_FRIENDS_NEWS                  = 'friends_actions.news';
    const SCOPE_USER_VIDEO                    = 'user_actions.video';
    const SCOPE_FRIENDS_VIDEO                 = 'friends_actions.video';
    const SCOPE_USER_APP                      = 'user_actions:APP_NAMESPACE';
    const SCOPE_FRIENDS_APP                   = 'friends_actions:APP_NAMESPACE';
    const SCOPE_USER_GAMES                    = 'user_games_activity';
    const SCOPE_FRIENDS_GAMES                 = 'friends_games_activity';
    //Page Permissions
    const SCOPE_PAGES                         = 'manage_pages';
    const SCOPE_PUBLISH_PAGES                 = 'publish_pages';

    public function __construct(
        CredentialsInterface $credentials,
        ClientInterface $httpClient,
        TokenStorageInterface $storage,
        $scopes = array(),
        UriInterface $baseApiUri = null,
        $apiVersion = ""
    ) {
        parent::__construct($credentials, $httpClient, $storage, $scopes, $baseApiUri, true, $apiVersion);

        if (null === $baseApiUri) {
            $this->baseApiUri = new Uri('https://graph.facebook.com'.$this->getApiVersionString().'/');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorizationEndpoint()
    {
        return new Uri('https://www.facebook.com'.$this->getApiVersionString().'/dialog/oauth');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessTokenEndpoint()
    {
        return new Uri('https://graph.facebook.com'.$this->getApiVersionString().'/oauth/access_token');
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessTokenResponse($responseBody)
    {
        // Facebook gives us a query string ... Oh wait. JSON is too simple, understand ?
        parse_str($responseBody, $data);

        if (null === $data || !is_array($data)) {
            throw new TokenResponseException('Unable to parse response.');
        } elseif (isset($data['error'])) {
            throw new TokenResponseException('Error in retrieving token: "' . $data['error'] . '"');
        }

        $token = new StdOAuth2Token();
        $token->setAccessToken($data['access_token']);
        
        if (isset($data['expires'])) {
            $token->setLifeTime($data['expires']);
        }

        if (isset($data['refresh_token'])) {
            $token->setRefreshToken($data['refresh_token']);
            unset($data['refresh_token']);
        }

        unset($data['access_token']);
        unset($data['expires']);

        $token->setExtraParams($data);

        return $token;
    }

    public function getDialogUri($dialogPath, array $parameters)
    {
        if (!isset($parameters['redirect_uri'])) {
            throw new Exception("Redirect uri is mandatory for this request");
        }
        $parameters['app_id'] = $this->credentials->getConsumerId();
        $baseUrl = self::WWW_URL .$this->getApiVersionString(). '/dialog/' . $dialogPath;
        $query = http_build_query($parameters);
        return new Uri($baseUrl . '?' . $query);
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopesDelimiter()
    {
        return ',';
    }
}
