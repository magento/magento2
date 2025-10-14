<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model\Report;

use DateTime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * Paypal Settlement Report model
 *
 * Perform fetching reports from remote servers with following saving them to database
 * Prepare report rows for \Magento\Paypal\Model\Report\Settlement\Row model
 *
 * @method string getReportDate()
 * @method \Magento\Paypal\Model\Report\Settlement setReportDate(string $value)
 * @method string getAccountId()
 * @method \Magento\Paypal\Model\Report\Settlement setAccountId(string $value)
 * @method string getFilename()
 * @method \Magento\Paypal\Model\Report\Settlement setFilename(string $value)
 * @method string getLastModified()
 * @method \Magento\Paypal\Model\Report\Settlement setLastModified(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Settlement extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Default PayPal SFTP host
     */
    const REPORTS_HOSTNAME = "reports.paypal.com";

    /**
     * Default PayPal SFTP host for sandbox mode
     */
    const SANDBOX_REPORTS_HOSTNAME = "reports.sandbox.paypal.com";

    /**
     * PayPal SFTP path
     */
    const REPORTS_PATH = "/ppreports/outgoing";

    /**
     * Original charset of old report files
     */
    const FILES_IN_CHARSET = "UTF-16";

    /**
     * Target charset of report files to be parsed
     */
    const FILES_OUT_CHARSET = "UTF-8";

    /**
     * Reports rows storage
     *
     * @var array
     */
    protected $_rows = [];

    /**
     * @var array
     */
    protected $_csvColumns = [
        'old' => [
            'section_columns' => [
                '' => 0,
                'TransactionID' => 1,
                'InvoiceID' => 2,
                'PayPalReferenceID' => 3,
                'PayPalReferenceIDType' => 4,
                'TransactionEventCode' => 5,
                'TransactionInitiationDate' => 6,
                'TransactionCompletionDate' => 7,
                'TransactionDebitOrCredit' => 8,
                'GrossTransactionAmount' => 9,
                'GrossTransactionCurrency' => 10,
                'FeeDebitOrCredit' => 11,
                'FeeAmount' => 12,
                'FeeCurrency' => 13,
                'CustomField' => 14,
                'ConsumerID' => 15,
            ],
            'rowmap' => [
                'TransactionID' => 'transaction_id',
                'InvoiceID' => 'invoice_id',
                'PayPalReferenceID' => 'paypal_reference_id',
                'PayPalReferenceIDType' => 'paypal_reference_id_type',
                'TransactionEventCode' => 'transaction_event_code',
                'TransactionInitiationDate' => 'transaction_initiation_date',
                'TransactionCompletionDate' => 'transaction_completion_date',
                'TransactionDebitOrCredit' => 'transaction_debit_or_credit',
                'GrossTransactionAmount' => 'gross_transaction_amount',
                'GrossTransactionCurrency' => 'gross_transaction_currency',
                'FeeDebitOrCredit' => 'fee_debit_or_credit',
                'FeeAmount' => 'fee_amount',
                'FeeCurrency' => 'fee_currency',
                'CustomField' => 'custom_field',
                'ConsumerID' => 'consumer_id',
            ],
        ],
        'new' => [
            'section_columns' => [
                '' => 0,
                'Transaction ID' => 1,
                'Invoice ID' => 2,
                'PayPal Reference ID' => 3,
                'PayPal Reference ID Type' => 4,
                'Transaction Event Code' => 5,
                'Transaction Initiation Date' => 6,
                'Transaction Completion Date' => 7,
                'Transaction  Debit or Credit' => 8,
                'Gross Transaction Amount' => 9,
                'Gross Transaction Currency' => 10,
                'Fee Debit or Credit' => 11,
                'Fee Amount' => 12,
                'Fee Currency' => 13,
                'Custom Field' => 14,
                'Consumer ID' => 15,
                'Payment Tracking ID' => 16,
                'Store ID' => 17,
            ],
            'rowmap' => [
                'Transaction ID' => 'transaction_id',
                'Invoice ID' => 'invoice_id',
                'PayPal Reference ID' => 'paypal_reference_id',
                'PayPal Reference ID Type' => 'paypal_reference_id_type',
                'Transaction Event Code' => 'transaction_event_code',
                'Transaction Initiation Date' => 'transaction_initiation_date',
                'Transaction Completion Date' => 'transaction_completion_date',
                'Transaction  Debit or Credit' => 'transaction_debit_or_credit',
                'Gross Transaction Amount' => 'gross_transaction_amount',
                'Gross Transaction Currency' => 'gross_transaction_currency',
                'Fee Debit or Credit' => 'fee_debit_or_credit',
                'Fee Amount' => 'fee_amount',
                'Fee Currency' => 'fee_currency',
                'Custom Field' => 'custom_field',
                'Consumer ID' => 'consumer_id',
                'Payment Tracking ID' => 'payment_tracking_id',
                'Store ID' => 'store_id',
            ],
        ],
    ];

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_tmpDirectory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Columns with DateTime data type
     *
     * @var array
     */
    private $dateTimeColumns = ['transaction_initiation_date', 'transaction_completion_date'];

    /**
     * Columns with amount type
     *
     * @var array
     */
    private $amountColumns = ['gross_transaction_amount', 'fee_amount'];

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ?\Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->_tmpDirectory = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Paypal\Model\ResourceModel\Report\Settlement::class);
    }

    /**
     * Stop saving process if file with same report date, account ID and last modified date was already ferched
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function beforeSave()
    {
        $this->_dataSaveAllowed = true;
        if ($this->getId()) {
            if ($this->getLastModified() == $this->getReportLastModified()) {
                $this->_dataSaveAllowed = false;
            }
        }
        $this->setLastModified($this->getReportLastModified());
        return parent::beforeSave();
    }

    /**
     * Goes to specified host/path and fetches reports from there.
     *
     * Save reports to database.
     *
     * @param \Magento\Framework\Filesystem\Io\Sftp $connection
     * @return int Number of report rows that were fetched and saved successfully
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fetchAndSave(\Magento\Framework\Filesystem\Io\Sftp $connection)
    {
        $fetched = 0;
        $listing = $this->_filterReportsList($connection->rawls());
        foreach ($listing as $filename => $attributes) {
            $localCsv = 'PayPal_STL_' . uniqid(\Magento\Framework\Math\Random::getRandomNumber()) . time() . '.csv';
            if ($connection->read($filename, $this->_tmpDirectory->getAbsolutePath($localCsv))) {
                if (!$this->_tmpDirectory->isWritable($localCsv)) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('We cannot create a target file for reading reports.')
                    );
                }

                $encoded = $this->_tmpDirectory->readFile($localCsv);
                $csvFormat = 'new';

                $fileEncoding = mb_detect_encoding($encoded);

                if (self::FILES_OUT_CHARSET != $fileEncoding) {
                    $decoded = @iconv($fileEncoding, self::FILES_OUT_CHARSET . '//IGNORE', $encoded);
                    $this->_tmpDirectory->writeFile($localCsv, $decoded);
                    $csvFormat = 'old';
                }

                // Set last modified date, this value will be overwritten during parsing
                if (isset($attributes['mtime'])) {
                    $date = new \DateTime();
                    $lastModified = $date->setTimestamp($attributes['mtime']);
                    $this->setReportLastModified(
                        $lastModified->format('Y-m-d H:i:s')
                    );
                }

                $this->setReportDate(
                    $this->_fileNameToDate($filename)
                )->setFilename(
                    $filename
                )->parseCsv(
                    $localCsv,
                    $csvFormat
                );

                if ($this->getAccountId()) {
                    $this->save();
                }

                if ($this->_dataSaveAllowed) {
                    $fetched += count($this->_rows);
                }
                // clean object and remove parsed file
                $this->unsetData();
                $this->_tmpDirectory->delete($localCsv);
            }
        }
        return $fetched;
    }

    /**
     * Connect to an SFTP server using specified configuration
     *
     * @param array $config
     * @return \Magento\Framework\Filesystem\Io\Sftp
     * @throws \InvalidArgumentException
     */
    public static function createConnection(array $config)
    {
        if (!isset($config['hostname'])
            || !isset($config['username'])
            || !isset($config['password'])
            || !isset($config['path'])
        ) {
            throw new \InvalidArgumentException('Required config elements: hostname, username, password, path');
        }
        $connection = new \Magento\Framework\Filesystem\Io\Sftp();
        $connection->open(
            ['host' => $config['hostname'], 'username' => $config['username'], 'password' => $config['password']]
        );
        $connection->cd($config['path']);
        return $connection;
    }

    /**
     * Parse CSV file and collect report rows
     *
     * @param string $localCsv Path to CSV file
     * @param string $format CSV format(column names)
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function parseCsv($localCsv, $format = 'new')
    {
        $this->_rows = [];

        $sectionColumns = $this->_csvColumns[$format]['section_columns'];
        $rowMap = $this->_csvColumns[$format]['rowmap'];

        $flippedSectionColumns = array_flip($sectionColumns);
        $stream = $this->_tmpDirectory->openFile($localCsv, 'r');
        while ($line = $stream->readCsv()) {
            if (empty($line)) {
                // The line was empty, so skip it.
                continue;
            }
            $lineType = $line[0];
            switch ($lineType) {
                case 'RH':
                    // Report header.
                    $lastModified = new \DateTime($line[1]);
                    $this->setReportLastModified(
                        $lastModified->format('Y-m-d H:i:s')
                    );
                    //$this->setAccountId($columns[2]); -- probably we'll just take that from the section header...
                    break;
                case 'FH':
                    // File header.
                    // Nothing interesting here, move along
                    break;
                case 'SH':
                    // Section header.
                    $this->setAccountId($line[3]);
                    $this->loadByAccountAndDate();
                    break;
                case 'CH':
                    // Section columns.
                    // In case ever the column order is changed, we will have the items recorded properly
                    // anyway. We have named, not numbered columns.
                    $count = count($line);
                    for ($i = 1; $i < $count; $i++) {
                        $sectionColumns[$line[$i]] = $i;
                    }
                    $flippedSectionColumns = array_flip($sectionColumns);
                    break;
                case 'SB':
                    // Section body.
                    $this->_rows[] = $this->getBodyItems($line, $flippedSectionColumns, $rowMap);
                    break;
                case 'SC':
                    // Section records count.
                case 'RC':
                    // Report records count.
                case 'SF':
                    // Section footer.
                case 'FF':
                    // File footer.
                case 'RF':
                    // Report footer.
                    // Nothing to see here, move along
                    break;
                default:
                    break;
            }
        }
        return $this;
    }

    /**
     * Parse columns from line of csv file
     *
     * @param array $line
     * @param array $sectionColumns
     * @param array $rowMap
     * @return array
     */
    private function getBodyItems(array $line, array $sectionColumns, array $rowMap)
    {
        $bodyItem = [];
        for ($i = 1, $count = count($line); $i < $count; $i++) {
            if (isset($rowMap[$sectionColumns[$i]])) {
                if (in_array($rowMap[$sectionColumns[$i]], $this->dateTimeColumns)) {
                    $line[$i] = $this->formatDateTimeColumns($line[$i]);
                }
                if (in_array($rowMap[$sectionColumns[$i]], $this->amountColumns)) {
                    $line[$i] = $this->formatAmountColumn($line[$i]);
                }
                $bodyItem[$rowMap[$sectionColumns[$i]]] = $line[$i];
            }
        }
        return $bodyItem;
    }

    /**
     * Format date columns in UTC
     *
     * @param string $lineItem
     * @return string
     */
    private function formatDateTimeColumns($lineItem)
    {
        /** @var DateTime $date */
        $date = new DateTime($lineItem, new \DateTimeZone('UTC'));
        return $date->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * Format amount columns
     *
     * PayPal api returns amounts in cents, hence the values need to be divided by 100
     *
     * @param string $lineItem
     * @return float
     */
    private function formatAmountColumn($lineItem)
    {
        return (int)$lineItem / 100;
    }

    /**
     * Load report by unique key (account + report date)
     *
     * @return $this
     */
    public function loadByAccountAndDate()
    {
        $this->getResource()->loadByAccountAndDate($this, $this->getAccountId(), $this->getReportDate());
        return $this;
    }

    /**
     * Return collected rows for further processing.
     *
     * @return array
     */
    public function getRows()
    {
        return $this->_rows;
    }

    /**
     * Return name for row column
     *
     * @param string $field Field name in row model
     * @return \Magento\Framework\Phrase|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getFieldLabel($field)
    {
        switch ($field) {
            case 'report_date':
                return __('Report Date');
            case 'account_id':
                return __('Merchant Account');
            case 'transaction_id':
                return __('Transaction ID');
            case 'invoice_id':
                return __('Invoice ID');
            case 'paypal_reference_id':
                return __('PayPal Reference ID');
            case 'paypal_reference_id_type':
                return __('PayPal Reference ID Type');
            case 'transaction_event_code':
                return __('Event Code');
            case 'transaction_event':
                return __('Event');
            case 'transaction_initiation_date':
                return __('Start Date');
            case 'transaction_completion_date':
                return __('Finish Date');
            case 'transaction_debit_or_credit':
                return __('Debit or Credit');
            case 'gross_transaction_amount':
                return __('Gross Amount');
            case 'fee_debit_or_credit':
                return __('Fee Debit or Credit');
            case 'fee_amount':
                return __('Fee Amount');
            case 'custom_field':
                return __('Custom');
            default:
                return $field;
        }
    }

    /**
     * Iterate through website configurations and collect all SFTP configurations
     *
     * Filter config values if necessary
     *
     * @param bool $automaticMode Whether to skip settings with disabled Automatic Fetching or not
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getSftpCredentials($automaticMode = false)
    {
        $configs = [];
        $uniques = [];
        foreach ($this->_storeManager->getStores() as $store) {
            /*@var $store \Magento\Store\Model\Store */
            $active = $this->_scopeConfig->isSetFlag(
                'paypal/fetch_reports/active',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
            if (!$active && $automaticMode) {
                continue;
            }
            $cfg = [
                'hostname' => $this->_scopeConfig->getValue(
                    'paypal/fetch_reports/ftp_ip',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                ),
                'path' => $this->_scopeConfig->getValue(
                    'paypal/fetch_reports/ftp_path',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                ),
                'username' => $this->_scopeConfig->getValue(
                    'paypal/fetch_reports/ftp_login',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                ),
                'password' => $this->_scopeConfig->getValue(
                    'paypal/fetch_reports/ftp_password',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                ),
                'sandbox' => $this->_scopeConfig->getValue(
                    'paypal/fetch_reports/ftp_sandbox',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $store
                ),
            ];
            if (empty($cfg['username']) || empty($cfg['password'])) {
                continue;
            }
            if (empty($cfg['hostname']) || $cfg['sandbox']) {
                $cfg['hostname'] = $cfg['sandbox'] ? self::SANDBOX_REPORTS_HOSTNAME : self::REPORTS_HOSTNAME;
            }
            if (empty($cfg['path']) || $cfg['sandbox']) {
                $cfg['path'] = self::REPORTS_PATH;
            }
            // avoid duplicates
            if (in_array($this->serializer->serialize($cfg), $uniques)) {
                continue;
            }
            $uniques[] = $this->serializer->serialize($cfg);
            $configs[] = $cfg;
        }
        return $configs;
    }

    /**
     * Converts a filename to date of report.
     *
     * @param string $filename
     * @return string
     */
    protected function _fileNameToDate($filename)
    {
        // Currently filenames look like STL-YYYYMMDD, so that is what we care about.
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $dateSnippet = substr(basename($filename), 4, 8);
        $result = substr($dateSnippet, 0, 4) . '-' . substr($dateSnippet, 4, 2) . '-' . substr($dateSnippet, 6, 2);
        return $result;
    }

    /**
     * Filter SFTP file list by filename format
     *
     * Single Account format = STL-yyyymmdd.sequenceNumber.version.format
     * Multiple Account format = STL-yyyymmdd.reportingWindow.sequenceNumber.totalFiles.version.format
     *
     * @param array $list List of files as per $connection->rawls()
     * @return array Trimmed down list of files
     */
    protected function _filterReportsList($list)
    {
        $result = [];
        $pattern = '/^STL-(\d{8,8})\.((\d{2,2})|(([A-Z])\.(\d{2,2})\.(\d{2,2})))\.(.{3,3})\.CSV$/';
        foreach ($list as $filename => $data) {
            if (preg_match($pattern, $filename)) {
                $result[$filename] = $data;
            }
        }
        return $result;
    }
}
