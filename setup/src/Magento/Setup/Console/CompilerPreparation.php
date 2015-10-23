<?php
/***
 * Copyright © 2015 Magento. All rights reserved.
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

    /**
     * @param \Zend\ServiceManager\ServiceManager $serviceManager
     */
    public function __construct(\Zend\ServiceManager\ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Determine whether a CLI command is for compilation, and if so, clear the directory
     *
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function handleCompilerEnvironment()
    {
        $input = new ArgvInput();
        $compilationCommands = [DiCompileCommand::NAME, DiCompileMultiTenantCommand::NAME];
        $cmdName = $input->getFirstArgument();
        $isHelpOption = $input->hasParameterOption('--help') || $input->hasParameterOption('-h');

        if (!in_array($cmdName, $compilationCommands) || $isHelpOption) {
            return;
        }

        $generationDir = ($cmdName === DiCompileMultiTenantCommand::NAME)
            ? $input->getParameterOption(DiCompileMultiTenantCommand::INPUT_KEY_GENERATION)
            : null;

        if (!$generationDir) {
            $mageInitParams = $this->serviceManager->get(InitParamListener::BOOTSTRAP_PARAM);
            $mageDirs = isset($mageInitParams[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS])
                ? $mageInitParams[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS]
                : [];
            $generationDir = (new DirectoryList(BP, $mageDirs))->getPath(DirectoryList::GENERATION);
        }

        $filesystemDriver = new File();
        if ($filesystemDriver->isExists($generationDir)) {
            $filesystemDriver->deleteDirectory($generationDir);
        }
    }
}
