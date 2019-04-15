<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Error;

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Error processor
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * phpcs:ignoreFile
 */
class Processor
{
    const MAGE_ERRORS_LOCAL_XML = 'local.xml';
    const MAGE_ERRORS_DESIGN_XML = 'design.xml';
    const DEFAULT_SKIN = 'default';
    const ERROR_DIR = 'pub/errors';

    /**
     * Page title
     *
     * @var string
     */
    public $pageTitle;

    /**
     * Skin URL
     *
     * @var string
     */
    public $skinUrl;

    /**
     * Base URL
     *
     * @var string
     */
    public $baseUrl;

    /**
     * Post data
     *
     * @var array
     */
    public $postData;

    /**
     * Report data
     *
     * @var array
     */
    public $reportData;

    /**
     * Report action
     *
     * @var string
     */
    public $reportAction;

    /**
     * Report ID
     *
     * @var int
     */
    public $reportId;

    /**
     * Report file
     *
     * @var string
     */
    protected $_reportFile;

    /**
     * Show error message
     *
     * @var bool
     */
    public $showErrorMsg;

    /**
     * Show message after sending email
     *
     * @var bool
     */
    public $showSentMsg;

    /**
     * Show form for sending
     *
     * @var bool
     */
    public $showSendForm;

    /**
     * @var string
     */
    public $reportUrl;

    /**
     * Server script name
     *
     * @var string
     */
    protected $_scriptName;

    /**
     * Is root
     *
     * @var bool
     */
    protected $_root;

    /**
     * Internal config object
     *
     * @var \stdClass
     */
    protected $_config;

    /**
     * Http response
     *
     * @var \Magento\Framework\App\Response\Http
     */
    protected $_response;

    /**
     * JSON serializer
     *
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Response\Http $response
     * @param Json $serializer
     */
    public function __construct(\Magento\Framework\App\Response\Http $response, Json $serializer = null)
    {
        $this->_response = $response;
        $this->_errorDir  = __DIR__ . '/';
        $this->_reportDir = dirname(dirname($this->_errorDir)) . '/var/report/';
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);

        if (!empty($_SERVER['SCRIPT_NAME'])) {
            if (in_array(basename($_SERVER['SCRIPT_NAME'], '.php'), ['404', '503', 'report'])) {
                $this->_scriptName = dirname($_SERVER['SCRIPT_NAME']);
            } else {
                $this->_scriptName = $_SERVER['SCRIPT_NAME'];
            }
        }

        $reportId = (isset($_GET['id'])) ? (int)$_GET['id'] : null;
        if ($reportId) {
            $this->loadReport($reportId);
        }

        $this->_indexDir = $this->_getIndexDir();
        $this->_root  = is_dir($this->_indexDir . 'app');

        $this->_prepareConfig();
        if (isset($_GET['skin'])) {
            $this->_setSkin($_GET['skin']);
        }
    }

    /**
     * Process no cache error
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function processNoCache()
    {
        $this->pageTitle = 'Error : cached config data is unavailable';
        $this->_response->setBody($this->_renderPage('nocache.phtml'));
        return $this->_response;
    }

    /**
     * Process 404 error
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function process404()
    {
        $this->pageTitle = 'Error 404: Not Found';
        $this->_response->setHttpResponseCode(404);
        $this->_response->setBody($this->_renderPage('404.phtml'));
        return $this->_response;
    }

    /**
     * Process 503 error
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function process503()
    {
        $this->pageTitle = 'Error 503: Service Unavailable';
        $this->_response->setHttpResponseCode(503);
        $this->_response->setBody($this->_renderPage('503.phtml'));
        return $this->_response;
    }

    /**
     * Process report
     *
     * @return \Magento\Framework\App\Response\Http
     */
    public function processReport()
    {
        $this->pageTitle = 'There has been an error processing your request';
        $this->_response->setHttpResponseCode(500);

        $this->showErrorMsg = false;
        $this->showSentMsg  = false;
        $this->showSendForm = false;
        $this->reportAction = $this->_config->action;
        $this->_setReportUrl();

        if ($this->reportAction == 'email') {
            $this->showSendForm = true;
            $this->sendReport();
        }
        $this->_response->setBody($this->_renderPage('report.phtml'));
        return $this->_response;
    }

    /**
     * Retrieve skin URL
     *
     * @return string
     */
    public function getViewFileUrl()
    {
        //The url needs to be updated base on Document root path.
        return $this->getBaseUrl() .
        str_replace(
            str_replace('\\', '/', $this->_indexDir),
            '',
            str_replace('\\', '/', $this->_errorDir)
        ) . $this->_config->skin . '/';
    }

    /**
     * Retrieve base host URL without path
     *
     * @return string
     */
    public function getHostUrl()
    {
        /**
         * Define server http host
         */
        $host = $this->resolveHostName();

        $isSecure = (!empty($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] !== 'off')
            || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && ($_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $url = ($isSecure ? 'https://' : 'http://') . $host;

        $port = explode(':', $host);
        if (isset($port[1]) && !in_array($port[1], [80, 443])
            && !preg_match('/.*?\:[0-9]+$/', $url)
        ) {
            $url .= ':' . $port[1];
        }
        return  $url;
    }

    /**
     * Resolve hostname
     *
     * @return string
     */
    private function resolveHostName() : string
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        } else {
            $host = 'localhost';
        }
        return $host;
    }

    /**
     * Retrieve base URL
     *
     * @param bool $param
     * @return string
     */
    public function getBaseUrl($param = false)
    {
        $path = $this->_scriptName;

        if ($param && !$this->_root) {
            $path = dirname($path);
        }

        $basePath = str_replace('\\', '/', dirname($path));
        return $this->getHostUrl() . ('/' == $basePath ? '' : $basePath) . '/';
    }

    /**
     * Retrieve client IP address
     *
     * @return string
     */
    protected function _getClientIp()
    {
        return (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : 'undefined';
    }

    /**
     * Get index dir
     *
     * @return string
     */
    protected function _getIndexDir()
    {
        $documentRoot = '';
        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $documentRoot = rtrim(realpath($_SERVER['DOCUMENT_ROOT']), '/');
        }
        return dirname($documentRoot . $this->_scriptName) . '/';
    }

    /**
     * Prepare config data
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareConfig()
    {
        $local  = $this->_loadXml(self::MAGE_ERRORS_LOCAL_XML);
        $design = $this->_loadXml(self::MAGE_ERRORS_DESIGN_XML);

        //initial settings
        $config = new \stdClass();
        $config->action         = '';
        $config->subject        = 'Store Debug Information';
        $config->email_address  = '';
        $config->trash          = 'leave';
        $config->skin           = self::DEFAULT_SKIN;

        //combine xml data to one object
        if ($design !== null && (string)$design->skin) {
            $this->_setSkin((string)$design->skin, $config);
        }
        if ($local !== null) {
            if ((string)$local->report->action) {
                $config->action = $local->report->action;
            }
            if ((string)$local->report->subject) {
                $config->subject = $local->report->subject;
            }
            if ((string)$local->report->email_address) {
                $config->email_address = $local->report->email_address;
            }
            if ((string)$local->report->trash) {
                $config->trash = $local->report->trash;
            }
            if ((string)$local->skin) {
                $this->_setSkin((string)$local->skin, $config);
            }
        }
        if ((string)$config->email_address == '' && (string)$config->action == 'email') {
            $config->action = '';
        }

        $this->_config = $config;
    }

    /**
     * Load xml file
     *
     * @param string $xmlFile
     * @return \SimpleXMLElement
     */
    protected function _loadXml($xmlFile)
    {
        $configPath = $this->_getFilePath($xmlFile);
        return ($configPath) ? simplexml_load_file($configPath) : null;
    }

    /**
     * Render page
     *
     * @param string $template
     * @return string
     */
    protected function _renderPage($template)
    {
        $baseTemplate = $this->_getTemplatePath('page.phtml');
        $contentTemplate = $this->_getTemplatePath($template);

        $html = '';
        if ($baseTemplate && $contentTemplate) {
            ob_start();
            require_once $baseTemplate;
            $html = ob_get_clean();
        }
        return $html;
    }

    /**
     * Find file path
     *
     * @param string $file
     * @param array $directories
     * @return string
     */
    protected function _getFilePath($file, $directories = null)
    {
        if ($directories === null) {
            $directories[] = $this->_errorDir;
        }

        foreach ($directories as $directory) {
            if (file_exists($directory . $file)) {
                return $directory . $file;
            }
        }
    }

    /**
     * Find template path
     *
     * @param string $template
     * @return string
     */
    protected function _getTemplatePath($template)
    {
        $directories[] = $this->_errorDir . $this->_config->skin . '/';

        if ($this->_config->skin != self::DEFAULT_SKIN) {
            $directories[] = $this->_errorDir . self::DEFAULT_SKIN . '/';
        }

        return $this->_getFilePath($template, $directories);
    }

    /**
     * Set report data
     *
     * @param array $reportData
     * @return void
     */
    protected function _setReportData($reportData)
    {
        $this->reportData = $reportData;

        if (!isset($reportData['url'])) {
            $this->reportData['url'] = '';
        } else {
            $this->reportData['url'] = $this->getHostUrl() . $reportData['url'];
        }

        if ($this->reportData['script_name']) {
            $this->_scriptName = $this->reportData['script_name'];
        }
    }

    /**
     * Create report
     *
     * @param array $reportData
     * @return string
     */
    public function saveReport($reportData)
    {
        $this->reportData = $reportData;
        $this->reportId   = abs((int)(microtime(true) * random_int(100, 1000)));
        $this->_reportFile = $this->_reportDir . '/' . $this->reportId;
        $this->_setReportData($reportData);

        if (!file_exists($this->_reportDir)) {
            @mkdir($this->_reportDir, 0777, true);
        }

        @file_put_contents($this->_reportFile, $this->serializer->serialize($reportData));

        if (isset($reportData['skin']) && self::DEFAULT_SKIN != $reportData['skin']) {
            $this->_setSkin($reportData['skin']);
        }
        $this->_setReportUrl();

        return $this->reportUrl;
    }

    /**
     * Get report
     *
     * @param int $reportId
     * @return void
     */
    public function loadReport($reportId)
    {
        $this->reportId = $reportId;
        $this->_reportFile = $this->_reportDir . '/' . $reportId;

        if (!file_exists($this->_reportFile) || !is_readable($this->_reportFile)) {
            header("Location: " . $this->getBaseUrl());
            die();
        }
        $this->_setReportData($this->serializer->unserialize(file_get_contents($this->_reportFile)));
    }

    /**
     * Send report
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function sendReport()
    {
        $this->pageTitle = 'Error Submission Form';

        $this->postData['firstName'] = (isset($_POST['firstname'])) ? trim(htmlspecialchars($_POST['firstname'])) : '';
        $this->postData['lastName']  = (isset($_POST['lastname'])) ? trim(htmlspecialchars($_POST['lastname'])) : '';
        $this->postData['email']     = (isset($_POST['email'])) ? trim(htmlspecialchars($_POST['email'])) : '';
        $this->postData['telephone'] = (isset($_POST['telephone'])) ? trim(htmlspecialchars($_POST['telephone'])) : '';
        $this->postData['comment']   = (isset($_POST['comment'])) ? trim(htmlspecialchars($_POST['comment'])) : '';

        if (isset($_POST['submit'])) {
            if ($this->_validate()) {
                $msg  = "URL: {$this->reportData['url']}\n"
                    . "IP Address: {$this->_getClientIp()}\n"
                    . "First Name: {$this->postData['firstName']}\n"
                    . "Last Name: {$this->postData['lastName']}\n"
                    . "Email Address: {$this->postData['email']}\n";
                if ($this->postData['telephone']) {
                    $msg .= "Telephone: {$this->postData['telephone']}\n";
                }
                if ($this->postData['comment']) {
                    $msg .= "Comment: {$this->postData['comment']}\n";
                }

                $subject = sprintf('%s [%s]', (string)$this->_config->subject, $this->reportId);
                @mail((string)$this->_config->email_address, $subject, $msg);

                $this->showSendForm = false;
                $this->showSentMsg  = true;
            } else {
                $this->showErrorMsg = true;
            }
        } else {
            $time = gmdate('Y-m-d H:i:s \G\M\T');

            $msg = "URL: {$this->reportData['url']}\n"
                . "IP Address: {$this->_getClientIp()}\n"
                . "Time: {$time}\n"
                . "Error:\n{$this->reportData[0]}\n\n"
                . "Trace:\n{$this->reportData[1]}";

            $subject = sprintf('%s [%s]', (string)$this->_config->subject, $this->reportId);
            @mail((string)$this->_config->email_address, $subject, $msg);

            if ($this->_config->trash == 'delete') {
                @unlink($this->_reportFile);
            }
        }
    }

    /**
     * Validate submitted post data
     *
     * @return bool
     */
    protected function _validate()
    {
        $email = preg_match(
            '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',
            $this->postData['email']
        );
        return ($this->postData['firstName'] && $this->postData['lastName'] && $email);
    }

    /**
     * Skin setter
     *
     * @param string $value
     * @param \stdClass $config
     * @return void
     */
    protected function _setSkin($value, \stdClass $config = null)
    {
        if (preg_match('/^[a-z0-9_]+$/i', $value) && is_dir($this->_errorDir . $value)) {
            if (!$config) {
                if ($this->_config) {
                    $config = $this->_config;
                }
            }
            if ($config) {
                $config->skin = $value;
            }
        }
    }

    /**
     * Set current report URL from current params
     *
     * @return void
     */
    protected function _setReportUrl()
    {
        if ($this->reportId && $this->_config && isset($this->_config->skin)) {
            $this->reportUrl = "{$this->getBaseUrl(true)}pub/errors/report.php?"
                . http_build_query(['id' => $this->reportId, 'skin' => $this->_config->skin]);
        }
    }
}
