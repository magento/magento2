<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

class CliColors {
    private $foreground_colors = array();
    private $background_colors = array();

    public function __construct() {
        // Set up shell colors
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['light_gray'] = '47';
    }

    /**
     * Returns colored string
     *
     * @param $string
     * @param null $foreground_color
     * @param null $background_color
     * @return string
     */
    public function getColoredString($string, $foreground_color = null, $background_color = null) {
        $colored_string = "";

        // Check if given foreground color found
        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .=  $string . "\033[0m";

        return $colored_string;
    }

    /**
     * Returns all foreground color names
     *
     * @return array
     */
    public function getForegroundColors() {
        return array_keys($this->foreground_colors);
    }

    /**
     * Returns all background color names
     *
     * @return array
     */
    public function getBackgroundColors() {
        return array_keys($this->background_colors);
    }
}

class PreInstallCheck {
    private $installedViaBrew             = false;
    private $filePath                     = '';
    private $seleniumJarVersion           = '';

    private $phpWebsite                   = 'http://php.net/manual/en/install.php';
    private $composerWebsite              = 'https://getcomposer.org/download/';
    private $javaWebsite                  = 'https://www.java.com/en/download/';
    private $allureCliWebsite             = 'https://docs.qameta.io/allure/latest/#_installing_a_commandline';
    private $seleniumWebsite              = 'http://www.seleniumhq.org/download/';
    private $chromeDriverWebsite          = 'https://sites.google.com/a/chromium.org/chromedriver/downloads';
    private $geckoDriverWebsite           = 'https://github.com/mozilla/geckodriver';
    private $phantomJsWebsite             = 'http://phantomjs.org/';

    private $phpSupportedVersion          = '7.1.0';
    private $composerSupportedVersion     = '1.3.0';
    private $javaSupportedVersion         = '1.8.0';
    private $allureCliSupportedVersion    = '2.3.0';
    private $seleniumSupportedVersion     = '3.6.0';
    private $chromeDriverSupportedVersion = '2.33.0';
    private $geckoDriverSupportedVersion  = '0.19.0';
    private $phantomJsSupportedVersion    = '2.1.0';

    private $getPhpVersion;
    private $getComposerVersion;
    private $getJavaVersion;
    private $getAllureCliVersion;
    private $getSeleniumVersion;
    private $getChromeDriverVersion;
    private $getGeckoDriverVersion;
    private $getPhantomJsVersion;

    private $phpVersion;
    private $composerVersion;
    private $javaVersion;
    private $allureCliVersion;
    private $seleniumVersion;
    private $chromeDriverVersion;
    private $geckoDriverVersion;
    private $phantomJsVersion;

    private $phpStatus;
    private $composerStatus;
    private $javaStatus;
    private $allureCliStatus;
    private $seleniumStatus;
    private $chromeDriverStatus;
    private $geckoDriverStatus;
    private $phantomJsStatus;

    function __construct() {
        $this->didYouInstallViaBrew();

        $this->getPhpVersion = shell_exec('php --version');
        $this->getComposerVersion           = shell_exec('composer --version');
        $this->getJavaVersion               = shell_exec("java -version 2>&1");
        $this->getAllureCliVersion          = shell_exec('allure --version');
        $this->getSeleniumVersion           = $this->getSeleniumVersion();
        $this->getChromeDriverVersion       = $this->getChromeDriverVersion();
        $this->getGeckoDriverVersion        = shell_exec('geckodriver --version');
        $this->getPhantomJsVersion          = $this->getPhantomJsVersion();

        $this->phpVersion                   = $this->parseVersion($this->getPhpVersion);
        $this->composerVersion              = $this->parseVersion($this->getComposerVersion);
        $this->javaVersion                  = $this->parseJavaVersion($this->getJavaVersion);
        $this->allureCliVersion             = $this->parseVersion($this->getAllureCliVersion);
        $this->seleniumVersion              = $this->parseVersion($this->getSeleniumVersion);
        $this->chromeDriverVersion          = $this->parseVersion($this->getChromeDriverVersion);
        $this->geckoDriverVersion           = $this->parseVersion($this->getGeckoDriverVersion);
        $this->phantomJsVersion             = $this->parseVersion($this->getPhantomJsVersion);

        // String of null Versions - For Testing
//        $this->phpVersion            = null;
//        $this->composerVersion       = null;
//        $this->javaVersion           = null;
//        $this->allureCliVersion      = null;
//        $this->seleniumVersion       = null;
//        $this->chromeDriverVersion   = null;
//        $this->geckoDriverVersion    = null;
//        $this->phantomJsVersion      = null;

        // String of invalid Versions - For Testing
//        $this->phpVersion            = '7.0.0';
//        $this->composerVersion       = '1.0.0';
//        $this->javaVersion           = '1.0.0';
//        $this->allureCliVersion      = '2.0.0';
//        $this->seleniumVersion       = '3.0.0';
//        $this->chromeDriverVersion   = '2.0.0';
//        $this->geckoDriverVersion    = '0.0.0';
//        $this->phantomJsVersion      = '2.0.0';

        $this->phpStatus          = $this->verifyVersion('PHP', $this->phpVersion, $this->phpSupportedVersion, $this->phpWebsite);
        $this->composerStatus     = $this->verifyVersion('Composer', $this->composerVersion, $this->composerSupportedVersion, $this->composerWebsite);
        $this->javaStatus         = $this->verifyVersion('Java', $this->javaVersion, $this->javaSupportedVersion, $this->javaWebsite);
        $this->allureCliStatus    = $this->verifyVersion('Allure CLI', $this->allureCliVersion, $this->allureCliSupportedVersion, $this->allureCliWebsite);
        $this->seleniumStatus     = $this->verifyVersion('Selenium Standalone Server', $this->seleniumVersion, $this->seleniumSupportedVersion, $this->seleniumWebsite);
        $this->chromeDriverStatus = $this->verifyVersion('ChromeDriver', $this->chromeDriverVersion, $this->chromeDriverSupportedVersion, $this->chromeDriverWebsite);
        $this->geckoDriverStatus  = $this->verifyVersion('GeckoDriver', $this->geckoDriverVersion, $this->geckoDriverSupportedVersion, $this->geckoDriverWebsite);
        $this->phantomJsStatus    = $this->verifyVersion('PhantomJS', $this->phantomJsVersion, $this->phantomJsSupportedVersion, $this->phantomJsWebsite);

        ECHO "\n";
        $mask = "|%-13.13s |%18.18s |%18.18s |%-23.23s |\n";
        printf("---------------------------------------------------------------------------------\n");
        printf($mask, ' Software', 'Supported Version', 'Installed Version', ' Status');
        printf("---------------------------------------------------------------------------------\n");
        printf($mask, ' PHP',          $this->phpSupportedVersion          . '+',          $this->phpVersion, ' ' . $this->phpStatus);
        printf($mask, ' Composer',     $this->composerSupportedVersion     . '+',     $this->composerVersion, ' ' . $this->composerStatus);
        printf($mask, ' Java',         $this->javaSupportedVersion         . '+',         $this->javaVersion, ' ' . $this->javaStatus);
        printf($mask, ' Allure CLI',   $this->allureCliSupportedVersion    . '+',    $this->allureCliVersion, ' ' . $this->allureCliStatus);
        printf($mask, ' Selenium',     $this->seleniumSupportedVersion     . '+',     $this->seleniumVersion, ' ' . $this->seleniumStatus);
        printf($mask, ' ChromeDriver', $this->chromeDriverSupportedVersion . '+', $this->chromeDriverVersion, ' ' . $this->chromeDriverStatus);
        printf($mask, ' GeckoDriver',  $this->geckoDriverSupportedVersion  . '+',  $this->geckoDriverVersion, ' ' . $this->geckoDriverStatus);
        printf($mask, ' PhantomJS',    $this->phantomJsSupportedVersion    . '+',    $this->phantomJsVersion, ' ' . $this->phantomJsStatus);
        printf("---------------------------------------------------------------------------------\n");
    }

    /**
     * Ask if they installed the Browser Drivers via Brew.
     * Brew installs things globally making them easier for us to access.
     */
    public function didYouInstallViaBrew()
    {
        ECHO "Did you install Selenium Server, ChromeDriver, GeckoDriver and PhantomJS using Brew? (y/n) ";
        $handle1 = fopen ("php://stdin","r");
        $line1 = fgets($handle1);
        if (trim($line1) != 'y') {
            ECHO "Where did you save the files? (ex /Users/first_last/Automation/) ";
            $handle2 = fopen ("php://stdin","r");
            $this->filePath = fgets($handle2);
            fclose($handle2);

            ECHO "Which selenium-server-standalone-X.X.X.jar file did you download? (ex 3.6.0) ";
            $handle3 = fopen ("php://stdin","r");
            $this->seleniumJarVersion = fgets($handle3);
            fclose($handle3);
            fclose($handle1);
            ECHO "\n";
        } else {
            $this->installedViaBrew = true;
            fclose($handle1);
            ECHO "\n";
        }
    }

    /**
     * Parse the string that is returned for the Version number only.
     *
     * @param $stdout
     * @return null
     */
    public function parseVersion($stdout)
    {
        preg_match("/\d+(?:\.\d+)+/", $stdout, $matches);

        if (!is_null($matches) && isset($matches[0])) {
            return $matches[0];
        } else {
            return null;
        }
    }

    /**
     * Parse the string that is returned for the Version number only.
     * The message Java returns differs from the others hence the separate function.
     *
     * @param $stdout
     * @return null
     */
    public function parseJavaVersion($stdout)
    {
        preg_match('/\"(.+?)\"/', $stdout, $output_array);

        if (!is_null($output_array)) {
            return $output_array[1];
        } else {
            return null;
        }
    }

    /**
     * Get the Selenium Server version based on how it was installed.
     *
     * @return string
     */
    public function getSeleniumVersion()
    {
        $this->installedViaBrew;
        $this->filePath;
        $this->seleniumJarVersion;

        if ($this->installedViaBrew) {
            return shell_exec('selenium-server --version');
        } else {
            $command = sprintf('java -jar %s/selenium-server-standalone-%s.jar --version', $this->filePath, $this->seleniumJarVersion);
            $command = str_replace(array("\r", "\n"), '', $command) . "\n";
            return shell_exec($command);
        }
    }

    /**
     * Get the ChromeDriver version based on how it was installed.
     *
     * @return string
     */
    public function getChromeDriverVersion()
    {
        $this->installedViaBrew;
        $this->filePath;

        if ($this->installedViaBrew) {
            return shell_exec('chromedriver --version');
        } else {
            $command = sprintf('%s/chromedriver --version', $this->filePath);
            $command = str_replace(array("\r", "\n"), '', $command) . "\n";
            return shell_exec($command);
        }
    }

    /**
     * Get the PhantomJS version based on how it was installed.
     *
     * @return string
     */
    public function getPhantomJsVersion()
    {
        $this->installedViaBrew;
        $this->filePath;

        if ($this->installedViaBrew) {
            return shell_exec('phantomjs --version');
        } else {
            $command = sprintf('%s/phantomjs --version', $this->filePath);
            $command = str_replace(array("\r", "\n"), '', $command) . "\n";
            return shell_exec($command);
        }
    }

    /**
     * Print a "Valid Version Detected" message in color.
     *
     * @param $softwareName
     */
    public function printValidVersion($softwareName)
    {
        $colors = new CliColors();
        $string = sprintf("%s detected. Version is supported!", $softwareName);
        ECHO $colors->getColoredString($string, "black", "green") . "\n";
    }

    /**
     * Print a "Upgraded Version Needed" message in color.
     *
     * @param $softwareName
     * @param $supportedVersion
     * @param $website
     */
    public function printUpgradeVersion($softwareName, $supportedVersion, $website)
    {
        $colors = new CliColors();
        $string = sprintf("Unsupported version of %s detected. Please upgrade to v%s+: %s", $softwareName, $supportedVersion, $website);
        ECHO $colors->getColoredString($string, "black", "yellow") . "\n";
    }

    /**
     * Print a "Not Installed. Install Required." message in color.
     *
     * @param $softwareName
     * @param $supportedVersion
     * @param $website
     */
    public function printNoInstalledVersion($softwareName, $supportedVersion, $website)
    {
        $colors = new CliColors();
        $string = sprintf("%s not detected. Please install v%s+: %s", $softwareName, $supportedVersion, $website);
        ECHO $colors->getColoredString($string, "black", "red") . "\n";
    }

    /**
     * Verify that the versions.
     * Print the correct status message.
     *
     * @param $softwareName
     * @param $installedVersion
     * @param $supportedVersion
     * @param $website
     * @return string
     */
    public function verifyVersion($softwareName, $installedVersion, $supportedVersion, $website)
    {
        if (is_null($installedVersion)) {
            $this->printNoInstalledVersion($softwareName, $supportedVersion, $website);
            return 'Installation Required!';
        } else if ($installedVersion >= $supportedVersion) {
            $this->printValidVersion($softwareName);
            return 'Correct Version!';
        } else {
            $this->printUpgradeVersion($softwareName, $supportedVersion, $website);
            return 'Upgrade Required!';
        }
    }
}

$preCheck = new PreInstallCheck();