<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Controller;

use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Package\Version\VersionParser;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Magento\Setup\Model\PhpInformation;
use Magento\Setup\Model\FilePermissions;

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
     */
    public function __construct(
        PhpInformation $phpInformation,
        FilePermissions $permissions,
        VersionParser $versionParser
    ) {
        $this->phpInformation = $phpInformation;
            $this->permissions = $permissions;
        $this->versionParser = $versionParser;
    }

    /**
     * Verifies php version
     *
     * @return JsonModel
     */
    public function phpVersionAction()
    {
        try{
            $requiredVersion = $this->phpInformation->getRequiredPhpVersion();
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
        $currentPhpVersion = new VersionConstraint('=', PHP_VERSION);
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
     * Verifies php verifications
     *
     * @return JsonModel
     */
    public function phpExtensionsAction()
    {
        try{
            $required = $this->phpInformation->getRequired();
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
}
