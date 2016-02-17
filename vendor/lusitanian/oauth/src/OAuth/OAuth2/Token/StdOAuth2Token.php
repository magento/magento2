<?php

namespace OAuth\OAuth2\Token;

use OAuth\Common\Token\AbstractToken;

/**
 * Standard OAuth2 token implementation.
 * Implements OAuth\OAuth2\Token\TokenInterface for any functionality that might not be provided by AbstractToken.
 */
class StdOAuth2Token extends AbstractToken implements TokenInterface
{
}
