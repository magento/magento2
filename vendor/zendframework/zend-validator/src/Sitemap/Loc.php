<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\Sitemap;

use Zend\Uri;
use Zend\Validator\AbstractValidator;

/**
 * Validates whether a given value is valid as a sitemap <loc> value
 *
 * @link       http://www.sitemaps.org/protocol.php Sitemaps XML format
 *
 * @see        Zend\Uri\Uri
 */
class Loc extends AbstractValidator
{
    /**
     * Validation key for not valid
     *
     */
    const NOT_VALID = 'sitemapLocNotValid';
    const INVALID   = 'sitemapLocInvalid';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::NOT_VALID => "The input is not a valid sitemap location",
        self::INVALID   => "Invalid type given. String expected",
    );

    /**
     * Validates if a string is valid as a sitemap location
     *
     * @link http://www.sitemaps.org/protocol.php#locdef <loc>
     *
     * @param  string  $value  value to validate
     * @return bool
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);
        $uri = Uri\UriFactory::factory($value);
        if (!$uri->isValid()) {
            $this->error(self::NOT_VALID);
            return false;
        }

        return true;
    }
}
