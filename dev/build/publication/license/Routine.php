<?php
/**
 * {license_notice}
 *
 * @category   build
 * @package    license
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Service routines for license-tool command line script
 * Routine run time functions
 *
 */
class Routine
{
    /**
     * List skipped directories
     *
     * @var null|array
     */
    public static $skipDirectories = null;

    /**
     * List skipped files
     *
     * @var null|array
     */
    public static $skipFiles = null;

    /**
     * Verbose output flag
     *
     * @var bool
     */
    public static $isVerbose = false;

    /**
     * Dry run flag
     *
     * @var bool
     */
    public static $dryRun = false;

    /**
     * File types
     *
     * @var array
     */
    public static $fileTypes = array(
        'xml'   => array(
            '*.xml', '*.xml.template', '*.xml.additional', '*.xml.dist', '*.xml.sample',
            '*.xsd', '*.mxml', '*.jmx', '*.jtl',
        ),
        'php'   => array('*.php', '*.php.dist', '*.php.sample'),
        'phtml' => array('*.phtml'),
        'html'  => array('*.html', '*.htm'),
        'css'   => array('*.css'),
        'js'    => array('*.js'),
        'flex'  => array('*.as'),
        'sql'   => array('*.sql'),
    );

    /**
     * Length of working directory
     *
     * @var int
     */
    protected static $_workingDirLen = 0;

    /**
     * @var int
     */
    protected static $_errorsCount = 0;

    /**
     * @var int
     */
    protected static $_updatedCount = 0;

    /**
     * @var int
     */
    protected static $_skippedCount = 0;


    /**
     * Walk through all file inside folder and sub folder. Filter found files by pattern.
     *
     * @static
     * @param string|array $paths
     * @param string|array $fileMasks
     * @param array $result
     * @param bool $allowRecursion
     * @return null
     */
    public static function globSearch($paths, $fileMasks, &$result, $allowRecursion = true)
    {
        if (empty($paths)) {
            return;
        }

        if (!is_array($paths)) {
            $paths = array($paths);
        }

        if (!is_array($fileMasks)) {
            $fileMasks = array($fileMasks);
        }

        foreach ($paths as $resource) {
            if (is_file($resource) && !self::_isFileSkipped($resource)) {
                $result[] = $resource;
                continue;
            }

            if (self::_isDirectorySkipped($resource)) {
                continue;
            }

            if ($allowRecursion) {
                self::globSearch(glob($resource . '/*', GLOB_ONLYDIR), $fileMasks, $result, true);
            }

            self::_filterFilesByMask($resource, $fileMasks, $result);
        }
    }

    /**
     * Filter directory by passed file mask. Results will be saved in $result variable.
     *
     * @static
     * @param $directory
     * @param $fileMasks
     * @param $result
     * @return null
     */
    protected static function _filterFilesByMask($directory, $fileMasks, &$result)
    {
        foreach ($fileMasks as $filesMask) {
            foreach (glob($directory . '/' . $filesMask) as $filename) {
                if (is_file($filename) && !self::_isFileSkipped($filename)) {
                    $result[] = $filename;
                }
            }
        }
    }

    /**
     * Filters passed array on skip path items marked by "!" sign
     *
     * @static
     * @param string $workingDir
     * @param array $list
     */
    protected static function _setSkippedPaths($workingDir, $list)
    {
        $paths = array();
        foreach ($list as $globPattern) {
            $path = $workingDir . DIRECTORY_SEPARATOR . $globPattern;
            $subPaths = glob($path, GLOB_BRACE);
            if (false === $subPaths) {
                throw new Exception("No real paths found by glob pattern: {$path}");
            }
            $paths = array_merge($paths, $subPaths);
        }
        $paths = array_unique($paths);

        foreach ($paths as $path) {
            $real = realpath($path);
            if (is_dir($real)) {
                self::$skipDirectories[] = $real;
            } elseif (is_file($real)) {
                self::$skipFiles[] = $real;
            }
        }
    }

    /**
     * Analyzes passed directory should it be skipped or not.
     *
     * @static
     * @param string $directory
     * @return bool
     */
    protected static function _isDirectorySkipped($directory)
    {
        $directory = realpath($directory) . DIRECTORY_SEPARATOR;
        foreach (self::$skipDirectories as $skipDir) {
            if (false !== strpos($directory, $skipDir . DIRECTORY_SEPARATOR)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Analyzes passed file should it be skipped or not.
     *
     * @static
     * @param string $filename
     * @return bool
     */
    protected static function _isFileSkipped($filename)
    {
        return in_array(realpath($filename), self::$skipFiles);
    }

    /**
     * Updates files in passed directory using license rules.
     * Could be run as validation process for files in dry run case.
     *
     * @static
     * @param string|array $directories
     * @param string|array $fileMasks
     * @param AbstractLicense $license
     * @param bool $recursive
     * @return null
     */
    public static function updateLicense($directories, $fileMasks, $license, $recursive = true)
    {
        $foundFiles = array();
        self::globSearch($directories, $fileMasks, $foundFiles, $recursive);

        foreach ($foundFiles as $filename) {
            $path = substr($filename, self::$_workingDirLen + 1);
            $contents = file_get_contents($filename);
            preg_match('#/\*\*(.*)\*/.*#Us', $contents, $matches);
            if (empty($contents) || !isset($matches[1])) {
                self::printLog("E {$path}\n");
                self::$_errorsCount += 1;
                continue;
            }

            $placeholders = array(
                ' * {license_notice}',
                '{copyright}',
                '{license_link}'
            );

            $changeset = array(
                $license->getNotice(),
                $license->getCopyright(),
                $license->getLink()
            );

            $docBlock = str_replace($placeholders, $changeset, $matches[1]);

            $newContents = preg_replace('#(/\*\*).*(\*/.*)#Us', '$1'. $docBlock . '$2', $contents, 1);

            if ($contents !== $newContents) {
                if (!self::$dryRun) {
                    file_put_contents($filename, $newContents);
                }
                self::printLog(". {$path}\n");
                self::$_updatedCount += 1;
            } else {
                self::printLog("S {$path}\n");
                self::$_skippedCount += 1;
            }
        }
    }

    /**
     * Prints logging messaged in case verbose mode enabled during run.
     *
     * @statict
     * @param string $msg
     * @return null
     */
    public static function printLog($msg)
    {
        if (self::$isVerbose) {
            echo $msg;
        }
    }

    /**
     * Create instance of license class which contains information about license
     *
     * @static
     * @throws Exception
     * @param string $license
     * @return AbstractLicense
     */
    public static function createLicenseInstance($license)
    {
        $licenseClassName = ucfirst(strtolower($license));
        if (!class_exists($licenseClassName)) {
            $licenseClassFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . $licenseClassName . '.php';
            if (!file_exists($licenseClassFile) || !is_readable($licenseClassFile)) {
                throw new Exception("Can't access license file: {$licenseClassFile}.\n");
            }

            include_once $licenseClassFile;

            if (!class_exists($licenseClassName)) {
                throw new Exception("Can't find license class: {$licenseClassName}.\n");
            }
        }

        $licenseObject = new $licenseClassName;

        if (!$licenseObject instanceof LicenseAbstract) {
            throw new Exception("License class does not have correct interface: {$licenseClassName}.\n");
        }

        return $licenseObject;
    }

    /**
     * Entry point of routine work
     *
     * @static
     * @param string $workingDir
     * @param array $config
     * @param array $blackList
     * @return null
     */
    public static function run($workingDir, $config, $blackList)
    {
        // various display parameters
        $workingDir = realpath($workingDir);
        self::$_workingDirLen = strlen($workingDir);
        self::$_errorsCount  = 0;
        self::$_updatedCount = 0;
        self::$_skippedCount = 0;

        // set black list
        self::$skipFiles = array();
        self::$skipDirectories = array();
        self::_setSkippedPaths($workingDir, $blackList);

        $licenseInstances = array();
        foreach ($config as $path => $types) {
            // whether to scan directory recursively
            $recursive = (isset($types['_recursive']) ? $types['_recursive'] : true);
            unset($types['_recursive']);

            // update licenses
            foreach ($types as $fileType => $licenseType) {
                if (!isset($licenseInstances[$licenseType])) {
                    $licenseInstances[$licenseType] = Routine::createLicenseInstance($licenseType);
                }
                Routine::updateLicense(
                    array($workingDir . ($path ? DIRECTORY_SEPARATOR . $path : '')),
                    Routine::$fileTypes[$fileType],
                    $licenseInstances[$licenseType],
                    $recursive
                );
            }
        }

        Routine::printLog(sprintf("\n" . 'Updated: %d; Skipped: %d; Errors: %d.' . "\n",
            self::$_updatedCount, self::$_skippedCount, self::$_errorsCount
        ));
        if (self::$_errorsCount || self::$_skippedCount) {
            throw new Exception('Failed: check skipped files or errors.' . "\n");
        }
        Routine::printLog('Success.' . "\n");
    }
}
