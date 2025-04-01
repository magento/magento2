<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-04-01 19:39:09
 */

namespace Diepxuan\SyncCRM\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const XML_PATH_API_TOKEN = 'diepxuan_synccrm/api_settings/api_token';
    const XML_PATH_API_URL   = 'diepxuan_synccrm/api_settings/api_url';

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var EncryptorInterface */
    protected $encryptor;

    public function __construct(
        Context $context
    ) {
        $this->scopeConfig = $context->getCoreConfig();
        $this->encryptor   = $context->getEncryptor();
    }

    public function getApiUrl($path = null)
    {
        $path   = trim($path, '/');
        $return = trim($this->scopeConfig->getValue(self::XML_PATH_API_URL, ScopeInterface::SCOPE_STORE), '/');
        $return .= "/{$path}";

        return trim($return, '/');
    }

    public function getApiToken()
    {
        $encryptedToken = $this->scopeConfig->getValue(self::XML_PATH_API_TOKEN, ScopeInterface::SCOPE_STORE);

        return $this->encryptor->decrypt($encryptedToken);
    }

    public function getScopeConfig(): ScopeConfigInterface
    {
        return $this->scopeConfig;
    }
}
