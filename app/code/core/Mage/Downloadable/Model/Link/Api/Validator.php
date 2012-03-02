<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Downloadable links validator
 *
 * @category    Mage
 * @package     Mage_Downloadable
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Downloadable_Model_Link_Api_Validator //extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Acceptable resourceTypes array
     * @var array
     */
    protected $_types = array('link', 'sample');

    /**
     * Acceptable upload types array
     * @var array
     */
    protected $_uploadTypes = array('file', 'url');

    /**
     * List of all attributes and names endings of validation functions
     *
     * @var array
     */
    protected $_defaultAttributes = array(
        'link' => array(
            'title' => 'Title',                         // $1
            'price' => 'Price',                         // $2
            'number_of_downloads' => 'NumOfDownloads',  // if no set is_unlimited to 1 $3
            'is_unlimited' => 'Unlimited',              // 1|0 $4
            'is_shareable' => 'Shareable',              // 1|0|2 (2) $5
            'type' => 'UploadType',                     // file|url (file) $6
            'file' => 'File',                           // array(name, base64_content) $7
            'link_url' => 'Url',                        // URL $8
            'sort_order' => 'Order',                    // int (0) $9
            'sample' => array(
                'type' => 'UploadType',                 // file|url (file) $6
                'file' => 'File',                       // array(name, base64_content) $7
                'url' => 'Url'                          // URL $8
            )
        ),
        'sample' => array(
            'title' => 'Title',                         // $1
            'type' => 'UploadType',                     // file|url (file) $6
            'file' => 'File',                           // array(name, base64_content) $7
            'sample_url' => 'Url',                      // URL $8
            'sort_order' => 'Order'                     // int (0) $9
        )
    );

    /**
     * Get resource types
     *
     * @return array
     */
    public function getResourceTypes()
    {
        return $this->_types;
    }

    /**
     * Validate resourceType, it should be one of (links|samples|link_samples)
     *
     * @param string $type
     * @return boolean
     */
    public function validateType($type)
    {
        if (!in_array($type, $this->getResourceTypes())) {
            throw new Exception('unknown_resource_type');
        }
        return true;
    }

    /**
     * Validate all parameters and loads default values for omitted parameters.
     *
     * @param array $resource
     * @param string $resourceType
     */
    public function validateAttributes(&$resource, $resourceType)
    {
        $fields = $this->_defaultAttributes[$resourceType];
        $this->_dispatch($resource, $fields);

        $this->completeCheck($resource, $resourceType);
    }

    /**
     * Final check
     *
     * @param array $resource
     * @param string $resourceType
     */
    public function completeCheck(&$resource, $resourceType)
    {
        if ($resourceType == 'link') {
            if ($resource['type'] == 'file') {
                $this->validateFileDetails($resource['file']);
            }
            if ($resource['type'] == 'url' && empty($resource['link_url'])) {
                throw new Exception('empty_url');
            }
            // sample
            if ($resource['sample']['type'] == 'file') {
                $this->validateFileDetails($resource['sample']['file']);
            }
            if ($resource['sample']['type'] == 'url' && empty($resource['sample']['url'])) {
                throw new Exception('empty_url');
            }
        }
        if ($resourceType == 'sample') {
            if ($resource['type'] == 'file') {
                $this->validateFileDetails($resource['file']);
            }
            if ($resource['type'] == 'url' && empty($resource['sample_url'])) {
                throw new Exception('empty_url');
            }
        }
    }

    /**
     * Validate variable, in case of fault throw exception
     *
     * @param mixed $var
     */
    public function validateFileDetails(&$var)
    {
        if (!isset ($var['name']) || !is_string($var['name']) || strlen($var['name']) === 0) {
            throw new Exception('no_filename');
        }
        if (!isset ($var['base64_content'])
            || !is_string($var['base64_content'])
            || strlen($var['base64_content']) === 0
        ) {
            throw new Exception('no_file_base64_content');
        }
    }

    /**
     * Runs all checks.
     *
     * @param array $resource
     * @param array $fields
     */
    protected function _dispatch(&$resource, $fields)
    {
        foreach ($fields as $name => $validator) {
            if (is_string($validator) && strlen($validator) > 0 && array_key_exists($name, $resource)) {
                $call = 'validate' . $validator;
                $this->$call($resource[$name]);
            }
            if (is_array($validator)) {
                $this->_dispatch($resource[$name], $validator);
            }
        }
    }

    /**
     * Validate variable, in case of fault loads default entity.
     *
     * @param string $var
     */
    public function validateTitle(&$var)
    {
        if (!is_string($var) || strlen($var) === 0) {
           throw new Exception('no_title');
        }
    }

    /**
     * Validate variable, in case of fault loads default entity.
     *
     * @param float $var
     */
    public function validatePrice(&$var)
    {
        $var = is_numeric($var)? floatval($var) : floatval(0);
    }

    /**
     * Validate variable, in case of fault loads default entity.
     *
     * @param int $var
     */
    public function validateNumOfDownloads(&$var)
    {
        $var = is_numeric($var)? intval($var) : 0;
    }

    /**
     * Validate variable, in case of fault loads default entity.
     *
     * @param int|boolean $var
     */
    public function validateUnlimited(&$var)
    {
        $var = ((is_numeric($var) && $var >= 0 && $var <= 1) || (is_bool($var)))? intval($var) : 0;
    }

    /**
     * Validate variable, in case of fault loads default entity.
     *
     * @param int $var
     */
    public function validateShareable(&$var)
    {
        $var = (is_numeric($var) && $var >= 0 && $var <= 2)? intval($var) : 2;
    }

    /**
     * Validate variable, in case of fault loads default entity.
     *
     * @param array $var
     */
    public function validateFile(&$var)
    {
        $var = is_array($var)? $var : null;
    }

    /**
     * Validate variable, in case of fault loads default entity.
     *
     * @param string $var
     */
    public function validateUrl(&$var)
    {

        if (is_string($var) && strlen($var) > 0) {
            $urlregex = "/^(https?|ftp)\:\/\/([a-z0-9+\!\*\(\)\,\;\?\&\=\$\_\.\-]+(\:[a-z0-9+\!\*\(\)\,\;\?\&\=\$\_\.\-]+)?@)?[a-z0-9\+\$\_\-]+(\.[a-z0-9+\$\_\-]+)*(\:[0-9]{2,5})?(\/([a-z0-9+\$\_\-]\.?)+)*\/?(\?[a-z\+\&\$\_\.\-][a-z0-9\;\:\@\/\&\%\=\+\$\_\.\-]*)?(#[a-z\_\.\-][a-z0-9\+\$\_\.\-]*)?$/i";
            if (!preg_match($urlregex, $var)) {
                throw new Exception('url_not_valid');
            }
        } else {
            $var = '';
        }
    }

    /**
     * Validate variable, in case of fault loads default entity.
     *
     * @param int $var
     */
    public function validateOrder(&$var)
    {
        $var = is_numeric($var)? intval($var) : 0;
    }

    /**
     * Validate variable, in case of fault loads default entity.
     *
     * @param string $var
     */
    public function validateUploadType(&$var)
    {
        $var = in_array($var, $this->_uploadTypes)? $var : 'file';
    }
}
