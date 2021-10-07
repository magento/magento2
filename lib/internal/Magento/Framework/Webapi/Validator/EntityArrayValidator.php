<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\Validator;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ConfigOptionsListConstants as ConfigConstants;
use Magento\Framework\Webapi\Request;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Webapi\Validator\EntityArrayValidator\InputArraySizeLimitValue;
use Magento\Framework\App\DeploymentConfig;

/**
 * Validates service input
 */
class EntityArrayValidator implements ServiceInputValidatorInterface
{
    private const ASYNC_PROCESSOR_PATH = "/\/async\/V\d\//";

    /**
     * Default limit for asynchronous request
     */
    private const DEFAULT_ASYNC_INPUT_ARRAY_SIZE_LIMIT = 5000;

    /**
     * @var int
     */
    private $complexArrayItemLimit;

    /**
     * @var InputArraySizeLimitValue
     */
    private $inputArraySizeLimitValue;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param int $complexArrayItemLimit
     * @param InputArraySizeLimitValue|null $inputArraySizeLimitValue
     * @param Request|null $request
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(
        int $complexArrayItemLimit,
        ?InputArraySizeLimitValue $inputArraySizeLimitValue = null,
        ?Request $request = null,
        ?DeploymentConfig $deploymentConfig = null
    ) {
        $this->complexArrayItemLimit = $complexArrayItemLimit;
        $this->inputArraySizeLimitValue = $inputArraySizeLimitValue ?? ObjectManager::getInstance()
                ->get(InputArraySizeLimitValue::class);
        $this->request = $request ?? ObjectManager::getInstance()->get(Request::class);
        $this->deploymentConfig = $deploymentConfig ?? ObjectManager::getInstance()->get(DeploymentConfig::class);
    }

    /**
     * @inheritDoc
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function validateComplexArrayType(string $className, array $items): void
    {
        $limit = $this->getLimit();
        if (count($items) > $limit) {
            throw new InvalidArgumentException(
                __(
                    'Maximum items of type "%type" is %max',
                    ['type' => $className, 'max' => $limit]
                )
            );
        }
    }

    /**
     * @inheritDoc
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    public function validateEntityValue(object $entity, string $propertyName, $value): void
    {
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

    /**
     * Returns limit
     *
     * @return int
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function getLimit(): int
    {
        return $this->inputArraySizeLimitValue->get() ??  ($this->isAsync()
            ? $this->deploymentConfig->get(
                ConfigConstants::CONFIG_PATH_WEBAPI_ASYNC_DEFAULT_INPUT_ARRAY_SIZE_LIMIT,
                self::DEFAULT_ASYNC_INPUT_ARRAY_SIZE_LIMIT
            )
            : $this->deploymentConfig->get(
                ConfigConstants::CONFIG_PATH_WEBAPI_SYNC_DEFAULT_INPUT_ARRAY_SIZE_LIMIT,
                $this->complexArrayItemLimit
            )
        );
    }
}
