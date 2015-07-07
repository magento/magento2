<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Composer\Package\Version\VersionParser;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Magento\Framework\Composer\ComposerInformation;
use Magento\Setup\Model\PhpInformation;
use Magento\Setup\Model\FilePermissions;

/**
 * Class Environment
 *
 * Provides information and checks about the environment.
 */
class Environment extends AbstractActionController
{
    /**
     * Model to determine PHP version, currently installed and required PHP extensions.
     *
     * @var \Magento\Setup\Model\PhpInformation
     */
    protected $phpInformation;

    /**
     * Version parser
     *
     * @var VersionParser
     */
    protected $versionParser;

    /**
     * Constructor
     *
     * @param PhpInformation $phpInformation
     * @param FilePermissions $permissions
     * @param VersionParser $versionParser
     * @param ComposerInformation $composerInformation
     */
    public function __construct(
        PhpInformation $phpInformation,
        FilePermissions $permissions,
        VersionParser $versionParser,
        ComposerInformation $composerInformation
    ) {
        $this->phpInformation = $phpInformation;
        $this->permissions = $permissions;
        $this->versionParser = $versionParser;
        $this->composerInformation = $composerInformation;
    }

    /**
     * Verifies php version
     *
     * @return JsonModel
     */
    public function phpVersionAction()
    {
        try {
            $requiredVersion = $this->composerInformation->getRequiredPhpVersion();
        } catch (\Exception $e) {
            return new JsonModel(
                [
                    'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                    'data' => [
                        'error' => 'phpVersionError',
                        'message' => 'Cannot determine required PHP version: ' . $e->getMessage()
                    ],
                ]
            );
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
        $data = [
            'responseType' => $responseType,
            'data' => [
                'required' => $requiredVersion,
                'current' => PHP_VERSION,
            ],
        ];
        return new JsonModel($data);
    }

    /**
     * Checks PHP settings
     *
     * @return JsonModel
     */
    public function phpSettingsAction()
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

        $data = [
            'responseType' => $responseType,
            'data' => $settings
        ];

        return new JsonModel($data);
    }

    /**
     * Verifies php verifications
     *
     * @return JsonModel
     */
    public function phpExtensionsAction()
    {
        try {
            $required = $this->composerInformation->getRequiredExtensions();
            $current = $this->phpInformation->getCurrent();

        } catch (\Exception $e) {
            return new JsonModel(
                [
                    'responseType' => ResponseTypeInterface::RESPONSE_TYPE_ERROR,
                    'data' => [
                        'error' => 'phpExtensionError',
                        'message' => 'Cannot determine required PHP extensions: ' . $e->getMessage()
                    ],
                ]
            );
        }
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        $missing = array_values(array_diff($required, $current));
        if ($missing) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
        }
        $data = [
            'responseType' => $responseType,
            'data' => [
                'required' => $required,
                'missing' => $missing,
            ],
        ];

        return new JsonModel($data);
    }

    /**
     * Verifies file permissions
     *
     * @return JsonModel
     */
    public function filePermissionsAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        if ($this->permissions->getMissingWritableDirectoriesForInstallation()) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
        }

        $data = [
            'responseType' => $responseType,
            'data' => [
                'required' => $this->permissions->getInstallationWritableDirectories(),
                'current' => $this->permissions->getInstallationCurrentWritableDirectories(),
            ],
        ];

        return new JsonModel($data);
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
