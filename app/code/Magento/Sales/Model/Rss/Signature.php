<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Model\Rss;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\DeploymentConfig;

/**
 * Class for generating signature.
 */
class Signature extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Version of encryption key.
     *
     * @var int
     */
    private $keyVersion;

    /**
     * Array of encryption keys.
     *
     * @var string[]
     */
    private $keys = [];

    /**
     * @var mixed
     */
    private $deploymentConfig;

    /**
     * @inheritdoc
     */
    public function __construct(
        \Magento\Framework\App\DeploymentConfig $deploymentConfig = null
    ) {
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
        // load all possible keys
        $this->keys = preg_split(
            '/\s+/s',
            (string)$this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY)
        );
        $this->keyVersion = count($this->keys) - 1;
    }

    /**
     * Get secret key.
     *
     * @return string
     */
    private function getSecretKey(): string
    {
        return (string)$this->keys[$this->keyVersion];
    }

    /**
     * Sign data.
     *
     * @param string $data
     * @return string
     */
    public function signData(string $data): string
    {
        return hash_hmac('sha256', $data, pack('H*', $this->getSecretKey()));
    }
}
