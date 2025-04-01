<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-04-01 19:27:43
 */

namespace Diepxuan\SyncCRM\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

/**
 * SyncCRM model.
 *
 * @api
 *
 * @see   Context
 */
class Context
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * @var Curl
     */
    protected $_curl;

    public function __construct(
        LoggerInterface $logger,
        EncryptorInterface $encryptor,
        ScopeConfigInterface $coreConfig,
        Curl $curl
    ) {
        $this->_logger     = $logger;
        $this->_encryptor  = $encryptor;
        $this->_coreConfig = $coreConfig;
        $this->_curl       = $curl;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return EncryptorInterface
     */
    public function getEncryptor()
    {
        return $this->_encryptor;
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getCoreConfig()
    {
        return $this->_coreConfig;
    }

    /**
     * @return Curl
     */
    public function getCurl()
    {
        return $this->_curl;
    }
}
