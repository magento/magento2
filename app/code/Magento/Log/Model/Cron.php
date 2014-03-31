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
 * @package     Magento_Log
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Log Cron Model
 *
 * @category   Magento
 * @package    Magento_Log
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Log\Model;

class Cron extends \Magento\Model\AbstractModel
{
    const XML_PATH_EMAIL_LOG_CLEAN_TEMPLATE = 'system/log/error_email_template';

    const XML_PATH_EMAIL_LOG_CLEAN_IDENTITY = 'system/log/error_email_identity';

    const XML_PATH_EMAIL_LOG_CLEAN_RECIPIENT = 'system/log/error_email';

    const XML_PATH_LOG_CLEAN_ENABLED = 'system/log/enabled';

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errors = array();

    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Log\Model\Log
     */
    protected $_log;

    /**
     * @var \Magento\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @param \Magento\Model\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Mail\Template\TransportBuilder $transportBuilder
     * @param Log $log
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Model\Context $context,
        \Magento\Registry $registry,
        \Magento\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Log\Model\Log $log,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_transportBuilder = $transportBuilder;
        $this->_log = $log;
        $this->_storeManager = $storeManager;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->inlineTranslation = $inlineTranslation;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Send Log Clean Warnings
     *
     * @return $this
     */
    protected function _sendLogCleanEmail()
    {
        if (!$this->_errors) {
            return $this;
        }
        if (!$this->_coreStoreConfig->getConfig(self::XML_PATH_EMAIL_LOG_CLEAN_RECIPIENT)) {
            return $this;
        }

        $this->inlineTranslation->suspend();

        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $this->_coreStoreConfig->getConfig(self::XML_PATH_EMAIL_LOG_CLEAN_TEMPLATE)
        )->setTemplateOptions(
            array(
                'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId()
            )
        )->setTemplateVars(
            array('warnings' => join("\n", $this->_errors))
        )->setFrom(
            $this->_coreStoreConfig->getConfig(self::XML_PATH_EMAIL_LOG_CLEAN_IDENTITY)
        )->addTo(
            $this->_coreStoreConfig->getConfig(self::XML_PATH_EMAIL_LOG_CLEAN_RECIPIENT)
        )->getTransport();

        $transport->sendMessage();

        $this->inlineTranslation->resume();

        return $this;
    }

    /**
     * Clean logs
     *
     * @return $this
     */
    public function logClean()
    {
        if (!$this->_coreStoreConfig->getConfigFlag(self::XML_PATH_LOG_CLEAN_ENABLED)) {
            return $this;
        }

        $this->_errors = array();

        try {
            $this->_log->clean();
        } catch (\Exception $e) {
            $this->_errors[] = $e->getMessage();
            $this->_errors[] = $e->getTrace();
        }

        $this->_sendLogCleanEmail();

        return $this;
    }
}
