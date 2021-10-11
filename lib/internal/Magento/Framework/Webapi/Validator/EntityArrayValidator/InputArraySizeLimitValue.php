<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator\EntityArrayValidator;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants as ConstantList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Webapi\Request;

/**
 * Value of the size limit of the input array for service input validator
 */
class InputArraySizeLimitValue
{
    private const ASYNC_PROCESSOR_PATH = "/\/async\/V\d\//";

    /**
     * Default limit for asynchronous request
     */
    private const DEFAULT_ASYNC_INPUT_ARRAY_SIZE_LIMIT = 5000;

    /**
     * @var int|null
     */
    private $value;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param Request $request
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        Request $request,
        DeploymentConfig $deploymentConfig
    ) {
        $this->request = $request;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Set value of input array size limit
     *
     * @param int|null $value
     */
    public function set(?int $value): void
    {
        $this->value = $value;
    }

    /**
     * Get value of input array size limit
     *
     * @return int|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function get(): ?int
    {
        return $this->value ?? ($this->isAsync()
                ? $this->deploymentConfig->get(
                    ConstantList::CONFIG_PATH_WEBAPI_ASYNC_DEFAULT_INPUT_ARRAY_SIZE_LIMIT,
                    self::DEFAULT_ASYNC_INPUT_ARRAY_SIZE_LIMIT
                )
                : $this->deploymentConfig->get(
                    ConstantList::CONFIG_PATH_WEBAPI_SYNC_DEFAULT_INPUT_ARRAY_SIZE_LIMIT
                )
            );
    }

    /**
     * Returns true if using asynchronous Webapi
     *
     * @return bool
     */
    private function isAsync(): bool
    {
        return preg_match(self::ASYNC_PROCESSOR_PATH, $this->request->getPathInfo()) === 1;
    }
}
