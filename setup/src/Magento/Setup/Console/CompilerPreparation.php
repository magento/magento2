<?php
/***
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console;


use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Setup\Console\Command\DiCompileCommand;
use Magento\Setup\Console\Command\DiCompileMultiTenantCommand;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Symfony\Component\Console\Input\ArgvInput;

class CompilerPreparation
{
    /** @var \Zend\ServiceManager\ServiceManager */
    private $serviceManager;

    /** @var ArgvInput */
    private $input;

    /** @var File */
    private $filesystemDriver;

    /**
     * @param \Zend\ServiceManager\ServiceManager $serviceManager
     * @param ArgvInput $input
     * @param File $filesystemDriver
     */
    public function __construct(
        \Zend\ServiceManager\ServiceManager $serviceManager,
        \Symfony\Component\Console\Input\ArgvInput $input,
        \Magento\Framework\Filesystem\Driver\File $filesystemDriver
    ) {
        $this->serviceManager   = $serviceManager;
        $this->input            = $input;
        $this->filesystemDriver = $filesystemDriver;
    }

    /**
     * Determine whether a CLI command is for compilation, and if so, clear the directory
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     * @return void
     */
    public function handleCompilerEnvironment()
    {
        $compilationCommands = [DiCompileCommand::NAME, DiCompileMultiTenantCommand::NAME];
        $cmdName = $this->input->getFirstArgument();
        $isHelpOption = $this->input->hasParameterOption('--help') || $this->input->hasParameterOption('-h');

        if (!in_array($cmdName, $compilationCommands) || $isHelpOption) {
            return;
        }

        $generationDir = ($cmdName === DiCompileMultiTenantCommand::NAME)
            ? $this->input->getParameterOption(DiCompileMultiTenantCommand::INPUT_KEY_GENERATION)
            : null;

        if (!$generationDir) {
            $mageInitParams = $this->serviceManager->get(InitParamListener::BOOTSTRAP_PARAM);
            $mageDirs = isset($mageInitParams[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS])
                ? $mageInitParams[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS]
                : [];
            $generationDir = (new DirectoryList(BP, $mageDirs))->getPath(DirectoryList::GENERATION);
        }

        if ($this->filesystemDriver->isExists($generationDir)) {
            $this->filesystemDriver->deleteDirectory($generationDir);
        }
    }
}
