<?php

class JsTestRunner
{
    /**
     * App root relative to the dev/tests/js dir
     */
    const RELATIVE_APP_ROOT = '../../..';

    /**
     * @var array
     */
    private $config;

    private function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Factory method
     *
     * If no config file path is specified it tries to determine
     * the configuration file to use automatically.
     *
     * @param string $configFile
     * @return JsTestRunner
     */
    public static function fromConfigFile($configFile = null)
    {
        $dir = __DIR__ . '/..';
        if (is_null($configFile)) {
            $userConfig = $dir . '/jsTestDriver.php';
            $defaultConfig = $dir . '/jsTestDriver.php.dist';
            $configFile = file_exists($userConfig) ? $userConfig : $defaultConfig;
        }
        return new self(require $configFile);
    }

    /**
     * Build the config file and execute all JavaScript tests
     * 
     * @return void
     */
    public function runTests()
    {
        $jsTestDriver = $this->getPathToJsTestDriverJarFromConfig();
        $browserCommand = $this->getBrowserCommand();
        $serverPort = $this->getServerPort();
        $testDriverConfigFile = $this->getJsTestDriverConfigFileName();
        $testOutputDir = $this->getOutputDir();
        
        $command = 'java -jar "' . $jsTestDriver . '"' .
            ' --config "' . $testDriverConfigFile . '"' . 
            ' --reset ' . 
            ' --port ' . $serverPort .
            ' --browser "' . $browserCommand . '"' .
            ' --raiseOnFailure true' .
            ' --tests all' . 
            ' --testOutput "' . $testOutputDir . '"';

        $this->writeJsTestRunnerConfig();
        $this->resetTestOutputDir($testOutputDir);
        $this->executeCommand($command);
    }

    /**
     * Fetch the JsTestDriver configuration setting from
     * the configuration array and check the file exists.
     * 
     * @return string
     */
    private function getPathToJsTestDriverJarFromConfig()
    {
        if (!isset($this->config['JsTestDriver'])) {
            echo "Value for the 'JsTestDriver' configuration parameter is not specified." . PHP_EOL;
            $this->showUsage();
        }
        $jsTestDriver = $this->config['JsTestDriver'];
        if (!file_exists($jsTestDriver)) {
            $this->reportError('JsTestDriver jar file does not exist: ' . $jsTestDriver);
        }
        return $jsTestDriver;
    }

    /**
     * Return the command to execute the browser
     *
     * If no browser is configured in the $this->config array, try
     * to determine the path to the browser automatically.
     * 
     * @return string
     */
    private function getBrowserCommand()
    {
        if (isset($this->config['Browser'])) {
            $browser = $this->config['Browser'];
        } else {
            $browser = $this->getDefaultBrowserOpenCommand();
        }
        return $browser;
    }

    /**
     * Return the command to start the browser depending on the os
     *
     * @return string
     */
    private function getDefaultBrowserOpenCommand()
    {
        if ($this->isWindows()) {
            $browser = 'C:\Program Files (x86)\Mozilla Firefox\firefox.exe';
        } elseif ($this->isOsX()) {
            $browser = 'open;-a;Safari';
        } else {
            $browser = exec('which firefox');
        }
        return $browser;
    }

    /**
     * Reports an error given an error message and exits, effectively halting the PHP script's execution.
     *
     * @param string $message - Error message to be displayed to the user.
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    private function reportError($message)
    {
        echo $message . PHP_EOL;
        exit(1);
    }

    /**
     * Show a message that displays how to use (invoke) this PHP script and exit.
     * 
     * @return void
     */
    private function showUsage()
    {
        $scriptName = $GLOBALS['argv'][0];
        $this->reportError('Usage: php ' . $scriptName);
    }

    /**
     * Recreate the JsTestRunner.conf file
     * 
     * @return void
     */
    public function writeJsTestRunnerConfig()
    {
        $serveFiles = $this->getServeFiles();

        $handle = fopen($this->getJsTestDriverConfigFileName(), 'w');
        $this->writeServerConfig($handle);
        $this->writeProxiesConfig($handle);
        $this->writeLoadFilesConfig($handle, $serveFiles);
        $this->writeTestFilesConfig($handle);
        $this->writeServeFilesConfig($handle, $serveFiles);
        fclose($handle);
    }

    /**
     * Accepts an array of directories and generates a list of Javascript files (.js) in those directories and
     * all subdirectories recursively.
     *
     * @param array $searchDirs An array of directories as specified in the configuration file (i.e. $configFile).
     *
     * @return array An array of directory paths to all Javascript files found by recursively searching the
     * specified array of directories.
     */
    private function listFiles(array $searchDirs)
    {
        $baseDir = $this->normalize(self::RELATIVE_APP_ROOT);
        $result = [];
        foreach ($searchDirs as $dir) {
            $path = $baseDir . $dir;
            if (is_file($path)) {
                $path = substr_replace($path, self::RELATIVE_APP_ROOT, 0, strlen($baseDir));
                array_push($result, $path);
            } else {
                $paths = glob($path . '/*', GLOB_ONLYDIR | GLOB_NOSORT);
                $paths = substr_replace($paths, '', 0, strlen($baseDir));
                $result = array_merge($result, $this->listFiles($paths));
    
                $files = glob($path . '/*.js', GLOB_NOSORT);
                $files = substr_replace($files, self::RELATIVE_APP_ROOT, 0, strlen($baseDir));
                $result = array_merge($result, $files);
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    private function getServerWithPort()
    {
        return isset($this->config['server']) ? $this->config['server'] : 'http://localhost:9876';
    }

    /**
     * @return string
     */
    private function getServerPort()
    {
        $server = $this->getServerWithPort();
        return substr(strrchr($server, ':'), 1);
    }

    /**
     * @return array
     */
    private function getProxies()
    {
        return isset($this->config['proxy']) ? $this->config['proxy'] : [];
    }

    /**
     * @param string $command
     * @return void
     */
    private function executeCommand($command)
    {
        echo $command . PHP_EOL;

        if ($this->isWindows()) {
            $this->executeCommandOnWindows($command);
        } else {
            $this->executeCommandOnUnix($command);
        }
        echo 'Test output can be found in "' . $this->getOutputDir() . '"' . PHP_EOL;
    }

    /**
     * @return bool
     */
    private function isOsX()
    {
        return PHP_OS === 'Darwin';
    }

    /**
     * @return bool
     */
    private function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }

    /**
     * @param string $command
     * @return void
     */
    private function executeCommandOnWindows($command)
    {
        system($command);
    }
    
    /**
     * @param string $command
     * @return void
     */
    private function executeCommandOnUnix($command)
    {
        $commandFile = __DIR__ . '/../run_js_tests.sh';
        $shellCommand = $this->getShellCommandScript($command);
        $this->writeShellCommandScript($commandFile, $shellCommand);
        system($commandFile);
    }

    /**
     * @param string $command
     * @return string
     */
    private function getShellCommandScript($command)
    {
        return 'LSOF=`/usr/sbin/lsof -i TCP:' . $this->getServerPort() . ' -t`
if [ "$LSOF" != "" ];
then
    kill -9 $LSOF
fi

if [ `uname` != Darwin ]; then
    DISPLAY_NUM=99
    ps -ef | egrep "[X]vfb.*:$DISPLAY_NUM"
    if [ $? -eq 0 ] ; then
        pkill Xvfb
    fi
    
    XVFB=`which Xvfb`
    if [ "$?" -eq 1 ];
    then
        echo "Xvfb not found."
        exit 1
    fi
    
    $XVFB :$DISPLAY_NUM -nolisten inet6 -ac & 
    PID_XVFB="$!"        # take the process ID
    export DISPLAY=:$DISPLAY_NUM   # set display to use that of the Xvfb
fi

USER=`whoami`
SUDO=`which sudo`

# run the tests
$SUDO -u $USER ' . $command . '

if [ "$PID_XVFB" != "" ]; then
    kill -9 $PID_XVFB    # shut down Xvfb (firefox will shut down cleanly by JsTestDriver)
fi
echo "Done."
';
    }

    /**
     * @param string $commandFile
     * @param string $shellCommand
     * @return void
     */
    private function writeShellCommandScript($commandFile, $shellCommand)
    {
        $handle = fopen($commandFile, 'w');
        fwrite($handle, $shellCommand . PHP_EOL);
        fclose($handle);
        chmod($commandFile, 0750);
    }

    /**
     * @param resource $handle
     * @return void
     */
    private function writeServerConfig($handle)
    {
        $server = $this->getServerWithPort();
        fwrite($handle, "server: $server" . PHP_EOL);
    }

    /**
     * @param resource $handle
     * @return void
     */
    private function writeProxiesConfig($handle)
    {
        $proxies = $this->getProxies();
        if (count($proxies) > 0) {
            fwrite($handle, "proxy:" . PHP_EOL);
            foreach ($proxies as $proxy) {
                fwrite($handle, $this->formatProxyConfig($proxy) . PHP_EOL);
            }
        }
    }
    
    /**
     * @param array $proxy
     * @return string
     */
    private function formatProxyConfig(array $proxy)
    {
        $proxyServer = $this->formatProxyServerTemplate($proxy['server']);
        $proxyDetails = sprintf('  - {matcher: "%s", server: "%s"}', $proxy['matcher'], $proxyServer);
        return $proxyDetails;
    }

    /**
     * @param string $proxy
     * @return string
     */
    private function formatProxyServerTemplate($proxy)
    {
        return sprintf($proxy, $this->getServerWithPort(), $this->normalize(self::RELATIVE_APP_ROOT));
    }

    /**
     * @param resource $handle
     * @param array $serveFiles
     * @return void
     */
    private function writeLoadFilesConfig($handle, array $serveFiles)
    {
        $sortedFiles = $this->getSortedLoadJsFiles();
        
        fwrite($handle, "load:" . PHP_EOL);
        foreach ($sortedFiles as $file) {
            if (!in_array($file, $serveFiles)) {
                fwrite($handle, "  - " . $file . PHP_EOL);
            }
        }
    }

    /**
     * @param resource $handle
     * @return void
     */
    private function writeTestFilesConfig($handle)
    {
        $testFilesPath = isset($this->config['test']) ? $this->config['test'] : [];
        $testFiles = $this->listFiles($testFilesPath);
        
        fwrite($handle, "test:" . PHP_EOL);
        foreach ($testFiles as $file) {
            fwrite($handle, "  - " . $file . PHP_EOL);
        }
    }

    /**
     * @param resource $handle
     * @param array $serveFiles
     * @return void
     */
    private function writeServeFilesConfig($handle, array $serveFiles)
    {
        if (count($serveFiles) > 0) {
            fwrite($handle, "serve:" . PHP_EOL);
            foreach ($serveFiles as $file) {
                fwrite($handle, "  - " . $file . PHP_EOL);
            }
        }
    }

    /**
     * @return string
     */
    private function getJsTestDriverConfigFileName()
    {
        return 'jsTestDriver.conf';
    }

    /**
     * @param string $testOutput
     * @return void
     */
    private function resetTestOutputDir($testOutput)
    {
        $filesystemAdapter = new \Magento\Framework\Filesystem\Driver\File();
        if ($filesystemAdapter->isExists($testOutput)) {
            $filesystemAdapter->deleteDirectory($testOutput);
        }
        mkdir($testOutput);
    }

    /**
     * List all files in the configuration array "load".
     * 
     * All files listed within the file jsTestDriverOrder.php will be
     * merged into the array, if they are not present there yet.
     * 
     * @return array
     */
    private function getSortedLoadJsFiles()
    {
        $loadFilesPath = isset($this->config['load']) ? $this->config['load'] : [];
        $loadFiles = $this->listFiles($loadFilesPath);
        if (empty($loadFiles)) {
            $this->reportError('Could not find any files to load.');
        }

        return $this->mergeLoadFilesWithOrderedLoadFileList($loadFiles);
    }

    /**
     * @param array $loadFiles
     * @return array
     */
    private function mergeLoadFilesWithOrderedLoadFileList($loadFiles)
    {
        $sortedFiles = [];

        $fileOrder = $this->normalize('jsTestDriverOrder.php');
        if (file_exists($fileOrder)) {
            $orderedLoadFiles = require $fileOrder;
            foreach ($orderedLoadFiles as $file) {
                $sortedFiles[] = self::RELATIVE_APP_ROOT . $file;
            }
            foreach ($loadFiles as $loadFile) {
                if (!$this->isNormalizedFileInList($loadFile, $orderedLoadFiles)) {
                    array_push($sortedFiles, $loadFile);
                }
            }
        }
        return $sortedFiles;
    }

    /**
     * Check if the file is in the given list of files.
     *
     * All files are normalized before comparison. 
     * 
     * @param string $searchFile
     * @param array $fileList
     * @return bool
     */
    private function isNormalizedFileInList($searchFile, array $fileList)
    {
        $found = false;
        $normalizedSearchFile = $this->normalize($searchFile);
        foreach ($fileList as $fileInList) {
            if (strcmp($this->normalize(self::RELATIVE_APP_ROOT . $fileInList), $normalizedSearchFile) == 0) {
                $found = true;
                break;
            }
        }
        return $found;
    }
    
    /**
     * Takes a file or directory path in any form and normalizes it to fully absolute canonical form
     * relative to the dev/tests/js directory.
     *
     * @param string $filePath - File or directory path to be fully normalized to canonical form.
     * @return string - The fully resolved path converted to absolute form.
     */
    private function normalize($filePath)
    {
        return str_replace('\\', '/', realpath(__DIR__ . '/../' . $filePath));
    }

    /**
     * Return the files from the directories configured in the config 'serve' section.
     * 
     * @return array
     */
    private function getServeFiles()
    {
        $serveFilesPath = isset($this->config['serve']) ? $this->config['serve'] : [];
        return $this->listFiles($serveFilesPath);
    }

    /**
     * @return string
     */
    private function getOutputDir()
    {
        return realpath(__DIR__ . '/../test-output');
    }
}
