<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Composer\Package\Version\VersionParser;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Convert\DataSize;
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
     * Data size converter
     *
     * @var DataSize
     */
    protected $dataSize;

    /**
     * Constructor
     *
     * @param ComposerInformation $composerInformation
     * @param PhpInformation $phpInformation
     * @param VersionParser $versionParser
     * @param DataSize $dataSize
     */
    public function __construct(
        ComposerInformation $composerInformation,
        PhpInformation $phpInformation,
        VersionParser $versionParser,
        DataSize $dataSize
    ) {
        $this->composerInformation = $composerInformation;
        $this->phpInformation = $phpInformation;
        $this->versionParser = $versionParser;
        $this->dataSize = $dataSize;
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
        $normalizedPhpVersion = $this->getNormalizedCurrentPhpVersion(PHP_VERSION);
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
            $this->checkXDebugNestedLevel(),
            $this->checkPopulateRawPostSetting(),
            $this->checkFunctionsExistence()
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
     * Checks PHP settings for cron
     *
     * @return array
     */
    public function checkPhpCronSettings()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;

        $settings = array_merge(
            $this->checkXDebugNestedLevel(),
            $this->checkMemoryLimit()
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
     * Checks php memory limit
     * @return array
     */
    public function checkMemoryLimit()
    {
        $data = [];
        $warning = false;
        $error = false;
        $message = '';
        $minimumRequiredMemoryLimit = '756M';
        $recommendedForUpgradeMemoryLimit = '2G';

        $currentMemoryLimit = ini_get('memory_limit');

        $currentMemoryInteger = (int)$currentMemoryLimit;

        if ($currentMemoryInteger > 0
            && $this->dataSize->convertSizeToBytes($currentMemoryLimit)
            < $this->dataSize->convertSizeToBytes($minimumRequiredMemoryLimit)
        ) {
            $error = true;
            $message = sprintf(
                'Your current PHP memory limit is %s.
                 Magento 2 requires it to be set to %s or more.
                 As a user with root privileges, edit your php.ini file to increase memory_limit.
                 (The command php --ini tells you where it is located.)
                 After that, restart your web server and try again.',
                $currentMemoryLimit,
                $minimumRequiredMemoryLimit
            );
        } elseif ($currentMemoryInteger > 0
            && $this->dataSize->convertSizeToBytes($currentMemoryLimit)
            < $this->dataSize->convertSizeToBytes($recommendedForUpgradeMemoryLimit)
        ) {
            $warning = true;
            $message = sprintf(
                'Your current PHP memory limit is %s.
                 We recommend it to be set to %s or more to use Setup Wizard.
                 As a user with root privileges, edit your php.ini file to increase memory_limit.
                 (The command php --ini tells you where it is located.)
                 After that, restart your web server and try again.',
                $currentMemoryLimit,
                $recommendedForUpgradeMemoryLimit
            );
        }

        $data['memory_limit'] = [
            'message' => $message,
            'error' => $error,
            'warning' => $warning,
        ];

        return $data;
    }

    /**
     * Checks if xdebug.max_nesting_level is set 200 or more
     * @return array
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    private function checkXDebugNestedLevel()
    {
        $data = [];
        $error = false;

        $currentExtensions = $this->phpInformation->getCurrent();
        if (in_array('xdebug', $currentExtensions)) {
            $currentXDebugNestingLevel = (int)ini_get('xdebug.max_nesting_level');
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

    /**
     * Checks if PHP version >= 5.6.0 and always_populate_raw_post_data is set to -1
     *
     * Beginning PHP 7.0, support for 'always_populate_raw_post_data' is going to removed.
     * And beginning PHP 5.6, a deprecated message is displayed if 'always_populate_raw_post_data'
     * is set to a value other than -1.
     *
     * @return array
     */
    private function checkPopulateRawPostSetting()
    {
        // HHVM and PHP 7does not support 'always_populate_raw_post_data' to be set to -1
        if (version_compare(PHP_VERSION, '7.0.0-beta') >= 0 || defined('HHVM_VERSION')) {
            return [];
        }

        $data = [];
        $error = false;
        $iniSetting = (int)ini_get('always_populate_raw_post_data');

        $checkVersionConstraint = $this->versionParser->parseConstraints('~5.6.0');
        $normalizedPhpVersion = $this->getNormalizedCurrentPhpVersion(PHP_VERSION);
        $currentVersion = $this->versionParser->parseConstraints($normalizedPhpVersion);
        if ($checkVersionConstraint->matches($currentVersion) && $iniSetting !== -1) {
            $error = true;
        }

        $message = sprintf(
            'Your PHP Version is %s, but always_populate_raw_post_data = %d.
            $HTTP_RAW_POST_DATA is deprecated from PHP 5.6 onwards and will be removed in PHP 7.0.
            This will stop the installer from running.
            Please open your php.ini file and set always_populate_raw_post_data to -1.
            If you need more help please call your hosting provider.',
            PHP_VERSION,
            (int)ini_get('always_populate_raw_post_data')
        );

        $data['always_populate_raw_post_data'] = [
            'message' => $message,
            'helpUrl' => 'http://php.net/manual/en/ini.core.php#ini.always-populate-settings-data',
            'error' => $error
        ];

        return $data;
    }

    /**
     * Check whether all special functions exists
     *
     * @return array
     */
    private function checkFunctionsExistence()
    {
        $data = [];
        $requiredFunctions = [
            [
                'name' => 'imagecreatefromjpeg',
                'message' => 'You must have installed GD library with --with-jpeg-dir=DIR option.',
                'helpUrl' => 'http://php.net/manual/en/image.installation.php',
            ],
        ];

        foreach ($requiredFunctions as $function) {
            $data['missed_function_' . $function['name']] = [
                'message' => $function['message'],
                'helpUrl' => $function['helpUrl'],
                'error' => !function_exists($function['name']),
            ];
        }

        return $data;
    }

    /**
     * Normalize PHP Version
     *
     * @param string $version
     * @return string
     */
    private function getNormalizedCurrentPhpVersion($version)
    {
        try {
            $normalizedPhpVersion = $this->versionParser->normalize($version);
        } catch (\UnexpectedValueException $e) {
            $prettyVersion = preg_replace('#^([^~+-]+).*$#', '$1', $version);
            $normalizedPhpVersion = $this->versionParser->normalize($prettyVersion);
        }
        return $normalizedPhpVersion;
    }
}
