<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            $this->checkPopulateRawPostSetting()
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
        $iniSetting = intVal(ini_get('always_populate_raw_post_data'));

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
            intVal(ini_get('always_populate_raw_post_data'))
        );

        $data['always_populate_raw_post_data'] = [
            'message' => $message,
            'helpUrl' => 'http://php.net/manual/en/ini.core.php#ini.always-populate-settings-data',
            'error' => $error
        ];

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
