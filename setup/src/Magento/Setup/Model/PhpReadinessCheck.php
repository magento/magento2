<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Composer\Package\Version\VersionParser;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Controller\ResponseTypeInterface;

/**
 * Checks for PHP readiness. It is used by both Cron and Setup wizard.
 */
class PhpReadinessCheck
{
    /**
     * @var ComposerInformation
     */
    private $composerInformation;

    /**
     * @var PhpInformation
     */
    private $phpInformation;

    /**
     * @var VersionParser
     */
    private $versionParser;

    /**
     * Constructor
     *
     * @param ComposerInformation $composerInformation
     * @param PhpInformation $phpInformation
     * @param VersionParser $versionParser
     */
    public function __construct(
        ComposerInformation $composerInformation,
        PhpInformation $phpInformation,
        VersionParser $versionParser
    ) {
        $this->composerInformation = $composerInformation;
        $this->phpInformation = $phpInformation;
        $this->versionParser = $versionParser;
    }

    /**
     * Checks PHP version
     *
     * @return array
     */
    public function checkPhpVersion()
    {
        try {
            $requiredVersion = $this->composerInformation->getRequiredPhpVersion();
        } catch (\Exception $e) {
            return [
                'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                'data' => [
                    'error' => 'phpVersionError',
                    'message' => 'Cannot determine required PHP version: ' . $e->getMessage()
                ],
            ];
        }
        $multipleConstraints = $this->versionParser->parseConstraints($requiredVersion);
        try {
            $normalizedPhpVersion = $this->versionParser->normalize(PHP_VERSION);
        } catch (\UnexpectedValueException $e) {
            $prettyVersion = preg_replace('#^([^~+-]+).*$#', '$1', PHP_VERSION);
            $normalizedPhpVersion = $this->versionParser->normalize($prettyVersion);
        }
        $currentPhpVersion = $this->versionParser->parseConstraints($normalizedPhpVersion);
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        if (!$multipleConstraints->matches($currentPhpVersion)) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
        }
        return [
            'responseType' => $responseType,
            'data' => [
                'required' => $requiredVersion,
                'current' => PHP_VERSION,
            ],
        ];
    }

    /**
     * Checks PHP settings
     *
     * @return array
     */
    public function checkPhpSettings()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;

        $settings = array_merge(
            $this->checkXDebugNestedLevel()
        );

        foreach ($settings as $setting) {
            if ($setting['error']) {
                $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
            }
        }

        return [
            'responseType' => $responseType,
            'data' => $settings
        ];
    }

    /**
     * Checks PHP extensions
     *
     * @return array
     */
    public function checkPhpExtensions()
    {
        try {
            $required = $this->composerInformation->getRequiredExtensions();
            $current = $this->phpInformation->getCurrent();
        } catch (\Exception $e) {
            return [
                'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                'data' => [
                    'error' => 'phpExtensionError',
                    'message' => 'Cannot determine required PHP extensions: ' . $e->getMessage()
                ],
            ];
        }
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        $missing = array_values(array_diff($required, $current));
        if ($missing) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
        }
        return [
            'responseType' => $responseType,
            'data' => [
                'required' => $required,
                'missing' => $missing,
            ],
        ];
    }

    /**
     * Checks if xdebug.max_nesting_level is set 200 or more
     * @return array
     */
    private function checkXDebugNestedLevel()
    {
        $data = [];
        $error = false;

        $currentExtensions = $this->phpInformation->getCurrent();
        if (in_array('xdebug', $currentExtensions)) {

            $currentXDebugNestingLevel = intval(ini_get('xdebug.max_nesting_level'));
            $minimumRequiredXDebugNestedLevel = $this->phpInformation->getRequiredMinimumXDebugNestedLevel();

            if ($minimumRequiredXDebugNestedLevel > $currentXDebugNestingLevel) {
                $error = true;
            }

            $message = sprintf(
                'Your current setting of xdebug.max_nesting_level=%d.
                 Magento 2 requires it to be set to %d or more.
                 Edit your config, restart web server, and try again.',
                $currentXDebugNestingLevel,
                $minimumRequiredXDebugNestedLevel
            );

            $data['xdebug_max_nesting_level'] = [
                'message' => $message,
                'error' => $error
            ];
        }

        return $data;
    }
}
