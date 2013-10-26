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
 * @category    Magento
 * @package     Magento_AdminNotification
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * AdminNotification survey model
 *
 * @category   Magento
 * @package    Magento_AdminNotification
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\AdminNotification\Model;

class Survey
{
    const SURVEY_URL = 'www.magentocommerce.com/instsurvey.html';

    /**
     * @var string
     */
    protected $_flagCode  = 'admin_notification_survey';

    /**
     * @var \Magento\Core\Model\Flag
     */
    protected $_flagModel = null;

    /**
     * @var \Magento\Core\Model\FlagFactory
     */
    protected $_flagFactory;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @param \Magento\Core\Model\FlagFactory $flagFactory
     * @param \Magento\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Core\Model\FlagFactory $flagFactory,
        \Magento\App\RequestInterface $request
    ) {
        $this->_request = $request;
        $this->_flagFactory = $flagFactory;
    }

    /**
     * Check if survey url valid (exists) or not
     *
     * @return bool
     */
    public function isSurveyUrlValid()
    {
        $curl = new \Magento\HTTP\Adapter\Curl();
        $curl->setConfig(array('timeout'   => 5))
            ->write(\Zend_Http_Client::GET, $this->getSurveyUrl(), '1.0');
        $response = $curl->read();
        $curl->close();

        if (\Zend_Http_Response::extractCode($response) == 200) {
            return true;
        }
        return false;
    }

    /**
     * Return survey url
     *
     * @return string
     */
    public function getSurveyUrl()
    {
        $host = $this->_request->isSecure() ? 'https://' : 'http://';
        return $host . self::SURVEY_URL;
    }

    /**
     * Return core flag model
     *
     * @return \Magento\Core\Model\Flag
     */
    protected function _getFlagModel()
    {
        if ($this->_flagModel === null) {
            $this->_flagModel = $this->_flagFactory->create(
                array('data' => array('flag_code' => $this->_flagCode)))
                ->loadSelf();
        }
        return $this->_flagModel;
    }

    /**
     * Check if survey question was already asked
     * or survey url was viewed during installation process
     *
     * @return boolean
     */
    public function isSurveyViewed()
    {
        $flagData = $this->_getFlagModel()->getFlagData();
        if (isset($flagData['survey_viewed']) && $flagData['survey_viewed'] == 1) {
            return true;
        }
        return false;
    }

    /**
     * Save survey viewed flag in core flag
     *
     * @param boolean $viewed
     */
    public function saveSurveyViewed($viewed)
    {
        $flagData = $this->_getFlagModel()->getFlagData();
        if (is_null($flagData)) {
            $flagData = array();
        }
        $flagData = array_merge($flagData, array('survey_viewed' => (bool)$viewed));
        $this->_getFlagModel()->setFlagData($flagData)->save();
    }
}
