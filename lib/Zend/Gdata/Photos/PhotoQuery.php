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
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: PhotoQuery.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Gdata_Gapps_Picasa_AlbumQuery
 */
#require_once('Zend/Gdata/Photos/AlbumQuery.php');

/**
 * Assists in constructing queries for comment/tag entries.
 * Instances of this class can be provided in many places where a URL is
 * required.
 *
 * For information on submitting queries to a server, see the
 * service class, Zend_Gdata_Photos.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Photos
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Photos_PhotoQuery extends Zend_Gdata_Photos_AlbumQuery
{

    /**
     * The ID of the photo to query for.
     *
     * @var string
     */
    protected $_photoId = null;

    /**
     * Set the photo ID to query for. When set, this photo's comments/tags
     * will be returned. If not set or null, the default user's feed will be
     * returned instead.
     *
     * @param string $value The ID of the photo to retrieve, or null to
     *          clear.
     */
     public function setPhotoId($value)
     {
         $this->_photoId = $value;
     }

    /**
     * Get the photo ID which is to be returned.
     *
     * @see setPhoto
     * @return string The ID of the photo to retrieve.
     */
    public function getPhotoId()
    {
        return $this->_photoId;
    }

    /**
     * Returns the URL generated for this query, based on it's current
     * parameters.
     *
     * @return string A URL generated based on the state of this query.
     * @throws Zend_Gdata_App_InvalidArgumentException
     */
    public function getQueryUrl($incomingUri = '')
    {
        $uri = '';
        if ($this->getPhotoId() !== null) {
            $uri .= '/photoid/' . $this->getPhotoId();
        } else {
            #require_once 'Zend/Gdata/App/InvalidArgumentException.php';
            throw new Zend_Gdata_App_InvalidArgumentException(
                    'PhotoId cannot be null');
        }
        $uri .= $incomingUri;
        return parent::getQueryUrl($uri);
    }

}
