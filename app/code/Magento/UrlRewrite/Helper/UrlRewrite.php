<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Backend\Model\Validator\UrlKey\CompositeUrlKey;
use Magento\Framework\Exception\LocalizedException;

class UrlRewrite extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Validation error constants
     */
    public const VERR_MANYSLASHES = 1;

    // Too many slashes in a row of request path, e.g. '///foo//'
    public const VERR_ANCHOR = 2;

    // Anchor is not supported in request path, e.g. 'foo#bar'

    /**
     * @var CompositeUrlKey
     */
    private $compositeUrlValidator;

    /**
     * @param CompositeUrlKey|null $compositeUrlValidator
     */
    public function __construct(
        CompositeUrlKey $compositeUrlValidator = null
    ) {
        $this->compositeUrlValidator = $compositeUrlValidator
            ?? ObjectManager::getInstance()->get(CompositeUrlKey::class);
    }

    /**
     * Core func to validate request path
     * If something is wrong with a path it throws localized error message and error code,
     * that can be checked to by wrapper func to alternate error message
     *
     * @param string $requestPath
     * @return bool
     * @throws \Exception
     */
    protected function _validateRequestPath($requestPath)
    {
        $requestPath = $requestPath !== null ? $requestPath : '';
        if (strpos($requestPath, '//') !== false) {
            throw new \Exception(
                __('Do not use two or more consecutive slashes in the request path.'),
                self::VERR_MANYSLASHES
            );
        }
        if (strpos($requestPath, '#') !== false) {
            throw new \Exception(__('Anchor symbol (#) is not supported in request path.'), self::VERR_ANCHOR);
        }
        $requestPathArray = explode('/', $requestPath);
        foreach ($requestPathArray as $requestPathPart) {
            $errors = $this->compositeUrlValidator->validate($requestPathPart);
            if (!empty($errors)) {
                throw new LocalizedException($errors[0]);
            }
        }
        return true;
    }

    /**
     * Validates request path
     *
     * Either returns TRUE (success) or throws error (validation failed)
     *
     * @param string $requestPath
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function validateRequestPath($requestPath)
    {
        try {
            $this->_validateRequestPath($requestPath);
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
        return true;
    }

    /**
     * Validates suffix for url rewrites to inform user about errors in it either returns TRUE
     * (success) or throws error (validation failed)
     *
     * @param string $suffix
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function validateSuffix($suffix)
    {
        try {
            // Suffix itself must be a valid request path
            $this->_validateRequestPath($suffix);
        } catch (\Exception $e) {
            // Make message saying about suffix, not request path
            switch ($e->getCode()) {
                case self::VERR_MANYSLASHES:
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Do not use two or more consecutive slashes in the url rewrite suffix.')
                    );
                case self::VERR_ANCHOR:
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Anchor symbol (#) is not supported in url rewrite suffix.')
                    );
            }
        }
        return true;
    }
}
