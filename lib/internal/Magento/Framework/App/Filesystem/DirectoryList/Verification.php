<?php
/**
 * The class, which verifies existence and write access to the needed application directories
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\App\Filesystem\DirectoryList;

use Magento\Framework\App\State;
use Magento\Framework\App\InitException;
use Magento\Framework\App\Filesystem;
use Magento\Framework\Filesystem\FilesystemException;

class Verification
{
    /**
     * Codes of directories to create and verify in production mode
     *
     * @var string[]
     */
    protected static $productionDirs = array(Filesystem::SESSION_DIR, Filesystem::CACHE_DIR, Filesystem::LOG_DIR);

    /**
     * Codes of directories to create and verify in non-production mode
     *
     * @var string[]
     */
    protected static $nonProductionDirs = array(Filesystem::SESSION_DIR, Filesystem::CACHE_DIR, Filesystem::LOG_DIR);

    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $filesystem;

    /**
     * Cached list of directories to create and verify write access
     *
     * @var string[]
     */
    protected $dirsToVerify = array();

    /**
     * Constructor - initialize object with required dependencies, determine application state
     *
     * @param Filesystem $filesystem
     * @param State $appState
     */
    public function __construct(Filesystem $filesystem, State $appState)
    {
        $this->filesystem = $filesystem;
        $this->dirsToVerify = $this->_getDirsToVerify($appState);
    }

    /**
     * Return list of directories, that must be verified according to the application mode
     *
     * @param State $appState
     * @return string[]
     */
    protected function _getDirsToVerify(State $appState)
    {
        $codes = $appState->getMode() == State::MODE_PRODUCTION ? self::$productionDirs : self::$nonProductionDirs;
        return $codes;
    }

    /**
     * Create the required directories, if they don't exist, and verify write access for existing directories
     *
     * @return void
     * @throws InitException
     */
    public function createAndVerifyDirectories()
    {
        $fails = array();
        foreach ($this->dirsToVerify as $code) {
            $directory = $this->filesystem->getDirectoryWrite($code);
            if ($directory->isExist()) {
                if (!$directory->isWritable()) {
                    $fails[] = $directory->getAbsolutePath();
                }
            } else {
                try {
                    $directory->create();
                } catch (FilesystemException $e) {
                    $fails[] = $directory->getAbsolutePath();
                }
            }
        }

        if ($fails) {
            $dirList = implode(', ', $fails);
            throw new InitException("Cannot create or verify write access: {$dirList}");
        }
    }
}
