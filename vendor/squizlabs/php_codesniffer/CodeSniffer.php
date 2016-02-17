<?php
/**
 * PHP_CodeSniffer tokenises PHP code and detects violations of a
 * defined set of coding standards.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

spl_autoload_register(array('PHP_CodeSniffer', 'autoload'));

if (class_exists('PHP_CodeSniffer_Exception', true) === false) {
    throw new Exception('Class PHP_CodeSniffer_Exception not found');
}

if (class_exists('PHP_CodeSniffer_File', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_File not found');
}

if (class_exists('PHP_CodeSniffer_Tokens', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Tokens not found');
}

if (class_exists('PHP_CodeSniffer_CLI', true) === false) {
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_CLI not found');
}

if (interface_exists('PHP_CodeSniffer_Sniff', true) === false) {
    throw new PHP_CodeSniffer_Exception('Interface PHP_CodeSniffer_Sniff not found');
}

/**
 * PHP_CodeSniffer tokenises PHP code and detects violations of a
 * defined set of coding standards.
 *
 * Standards are specified by classes that implement the PHP_CodeSniffer_Sniff
 * interface. A sniff registers what token types it wishes to listen for, then
 * PHP_CodeSniffer encounters that token, the sniff is invoked and passed
 * information about where the token was found in the stack, and the token stack
 * itself.
 *
 * Sniff files and their containing class must be prefixed with Sniff, and
 * have an extension of .php.
 *
 * Multiple PHP_CodeSniffer operations can be performed by re-calling the
 * process function with different parameters.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class PHP_CodeSniffer
{

    /**
     * The current version.
     *
     * @var string
     */
    const VERSION = '1.5.3';

    /**
     * Package stability; either stable or beta.
     *
     * @var string
     */
    const STABILITY = 'stable';

    /**
     * The file or directory that is currently being processed.
     *
     * @var string
     */
    protected $file = '';

    /**
     * A cache of different token types, resolved into arrays.
     *
     * @var array()
     * @see standardiseToken()
     */
    private static $_resolveTokenCache = array();

    /**
     * The directories that the processed rulesets are in.
     *
     * This is declared static because it is also used in the
     * autoloader to look for sniffs outside the PHPCS install.
     * This way, standards designed to be installed inside PHPCS can
     * also be used from outside the PHPCS Standards directory.
     *
     * @var string
     */
    protected static $rulesetDirs = array();

    /**
     * The CLI object controlling the run.
     *
     * @var PHP_CodeSniffer_CLI
     */
    public $cli = null;

    /**
     * The Reporting object controlling report generation.
     *
     * @var PHP_CodeSniffer_Reporting
     */
    public $reporting = null;

    /**
     * An array of sniff objects that are being used to check files.
     *
     * @var array(PHP_CodeSniffer_Sniff)
     */
    protected $listeners = array();

    /**
     * An array of sniffs that are being used to check files.
     *
     * @var array(string)
     */
    protected $sniffs = array();

    /**
     * The listeners array, indexed by token type.
     *
     * @var array
     */
    private $_tokenListeners = array();

    /**
     * An array of rules from the ruleset.xml file.
     *
     * It may be empty, indicating that the ruleset does not override
     * any of the default sniff settings.
     *
     * @var array
     */
    protected $ruleset = array();

    /**
     * An array of patterns to use for skipping files.
     *
     * @var array
     */
    protected $ignorePatterns = array();

    /**
     * An array of extensions for files we will check.
     *
     * @var array
     */
    public $allowedFileExtensions = array(
                                     'php' => 'PHP',
                                     'inc' => 'PHP',
                                     'js'  => 'JS',
                                     'css' => 'CSS',
                                    );

    /**
     * An array of variable types for param/var we will check.
     *
     * @var array(string)
     */
    public static $allowedTypes = array(
                                   'array',
                                   'boolean',
                                   'float',
                                   'integer',
                                   'mixed',
                                   'object',
                                   'string',
                                   'resource',
                                   'callable',
                                  );


    /**
     * Constructs a PHP_CodeSniffer object.
     *
     * @param int    $verbosity   The verbosity level.
     *                            1: Print progress information.
     *                            2: Print tokenizer debug information.
     *                            3: Print sniff debug information.
     * @param int    $tabWidth    The number of spaces each tab represents.
     *                            If greater than zero, tabs will be replaced
     *                            by spaces before testing each file.
     * @param string $encoding    The charset of the sniffed files.
     *                            This is important for some reports that output
     *                            with utf-8 encoding as you don't want it double
     *                            encoding messages.
     * @param bool   $interactive If TRUE, will stop after each file with errors
     *                            and wait for user input.
     *
     * @see process()
     */
    public function __construct(
        $verbosity=0,
        $tabWidth=0,
        $encoding='iso-8859-1',
        $interactive=false
    ) {
        if (defined('PHP_CODESNIFFER_VERBOSITY') === false) {
            define('PHP_CODESNIFFER_VERBOSITY', $verbosity);
        }

        if (defined('PHP_CODESNIFFER_TAB_WIDTH') === false) {
            define('PHP_CODESNIFFER_TAB_WIDTH', $tabWidth);
        }

        if (defined('PHP_CODESNIFFER_ENCODING') === false) {
            define('PHP_CODESNIFFER_ENCODING', $encoding);
        }

        if (defined('PHP_CODESNIFFER_INTERACTIVE') === false) {
            define('PHP_CODESNIFFER_INTERACTIVE', $interactive);
        }

        if (defined('PHPCS_DEFAULT_ERROR_SEV') === false) {
            define('PHPCS_DEFAULT_ERROR_SEV', 5);
        }

        if (defined('PHPCS_DEFAULT_WARN_SEV') === false) {
            define('PHPCS_DEFAULT_WARN_SEV', 5);
        }

        // Set default CLI object in case someone is running us
        // without using the command line script.
        $this->cli = new PHP_CodeSniffer_CLI();
        $this->cli->errorSeverity   = PHPCS_DEFAULT_ERROR_SEV;
        $this->cli->warningSeverity = PHPCS_DEFAULT_WARN_SEV;
        $this->cli->dieOnUnknownArg = false;

        $this->reporting = new PHP_CodeSniffer_Reporting();

    }//end __construct()


    /**
     * Autoload static method for loading classes and interfaces.
     *
     * @param string $className The name of the class or interface.
     *
     * @return void
     */
    public static function autoload($className)
    {
        if (substr($className, 0, 4) === 'PHP_') {
            $newClassName = substr($className, 4);
        } else {
            $newClassName = $className;
        }

        $path = str_replace(array('_', '\\'), '/', $newClassName).'.php';

        if (is_file(dirname(__FILE__).'/'.$path) === true) {
            // Check standard file locations based on class name.
            include dirname(__FILE__).'/'.$path;
        } else if (is_file(dirname(__FILE__).'/CodeSniffer/Standards/'.$path) === true) {
            // Check for included sniffs.
            include dirname(__FILE__).'/CodeSniffer/Standards/'.$path;
        } else {
            // Check standard file locations based on the loaded rulesets.
            foreach (self::$rulesetDirs as $rulesetDir) {
                if (is_file(dirname($rulesetDir).'/'.$path) === true) {
                    include_once dirname($rulesetDir).'/'.$path;
                    return;
                }
            }

            // Everything else.
            @include $path;
        }

    }//end autoload()


    /**
     * Sets an array of file extensions that we will allow checking of.
     *
     * If the extension is one of the defaults, a specific tokenizer
     * will be used. Otherwise, the PHP tokenizer will be used for
     * all extensions passed.
     *
     * @param array $extensions An array of file extensions.
     *
     * @return void
     */
    public function setAllowedFileExtensions(array $extensions)
    {
        $newExtensions = array();
        foreach ($extensions as $ext) {
            if (isset($this->allowedFileExtensions[$ext]) === true) {
                $newExtensions[$ext] = $this->allowedFileExtensions[$ext];
            } else {
                $newExtensions[$ext] = 'PHP';
            }
        }

        $this->allowedFileExtensions = $newExtensions;

    }//end setAllowedFileExtensions()


    /**
     * Sets an array of ignore patterns that we use to skip files and folders.
     *
     * Patterns are not case sensitive.
     *
     * @param array $patterns An array of ignore patterns. The pattern is the key
     *                        and the value is either "absolute" or "relative",
     *                        depending on how the pattern should be applied to a
     *                        file path.
     *
     * @return void
     */
    public function setIgnorePatterns(array $patterns)
    {
        $this->ignorePatterns = $patterns;

    }//end setIgnorePatterns()


    /**
     * Gets the array of ignore patterns.
     *
     * Optionally takes a listener to get ignore patterns specified
     * for that sniff only.
     *
     * @param string $listener The listener to get patterns for. If NULL, all
     *                         patterns are returned.
     *
     * @return array
     */
    public function getIgnorePatterns($listener=null)
    {
        if ($listener === null) {
            return $this->ignorePatterns;
        }

        if (isset($this->ignorePatterns[$listener]) === true) {
            return $this->ignorePatterns[$listener];
        }

        return array();

    }//end getIgnorePatterns()


    /**
     * Sets the internal CLI object.
     *
     * @param object $cli The CLI object controlling the run.
     *
     * @return void
     */
    public function setCli($cli)
    {
        $this->cli = $cli;

    }//end setCli()


    /**
     * Processes the files/directories that PHP_CodeSniffer was constructed with.
     *
     * @param string|array $files        The files and directories to process. For
     *                                   directories, each sub directory will also
     *                                   be traversed for source files.
     * @param string|array $standards    The set of code sniffs we are testing
     *                                   against.
     * @param array        $restrictions The sniff codes to restrict the
     *                                   violations to.
     * @param boolean      $local        If true, don't recurse into directories.
     *
     * @return void
     * @throws PHP_CodeSniffer_Exception If files or standard are invalid.
     */
    public function process($files, $standards, array $restrictions=array(), $local=false)
    {
        if (is_array($files) === false) {
            $files = array($files);
        }

        if (is_array($standards) === false) {
            $standards = array($standards);
        }

        // Reset the members.
        $this->listeners       = array();
        $this->sniffs          = array();
        $this->ruleset         = array();
        $this->_tokenListeners = array();
        self::$rulesetDirs     = array();

        // Ensure this option is enabled or else line endings will not always
        // be detected properly for files created on a Mac with the /r line ending.
        ini_set('auto_detect_line_endings', true);

        $sniffs = array();
        foreach ($standards as $standard) {
            $installed = $this->getInstalledStandardPath($standard);
            if ($installed !== null) {
                $standard = $installed;
            } else if (is_dir($standard) === true
                && is_file(realpath($standard.'/ruleset.xml')) === true
            ) {
                $standard = realpath($standard.'/ruleset.xml');
            }

            if (PHP_CODESNIFFER_VERBOSITY === 1) {
                $ruleset = simplexml_load_file($standard);
                if ($ruleset !== false) {
                    $standardName = (string) $ruleset['name'];
                }

                echo "Registering sniffs in the $standardName standard... ";
                if (count($standards) > 1 || PHP_CODESNIFFER_VERBOSITY > 2) {
                    echo PHP_EOL;
                }
            }

            $sniffs = array_merge($sniffs, $this->processRuleset($standard));
        }//end foreach

        $sniffRestrictions = array();
        foreach ($restrictions as $sniffCode) {
            $parts = explode('.', strtolower($sniffCode));
            $sniffRestrictions[] = $parts[0].'_sniffs_'.$parts[1].'_'.$parts[2].'sniff';
        }

        $this->registerSniffs($sniffs, $sniffRestrictions);
        $this->populateTokenListeners();

        if (PHP_CODESNIFFER_VERBOSITY === 1) {
            $numSniffs = count($this->sniffs);
            echo "DONE ($numSniffs sniffs registered)".PHP_EOL;
        }

        // The SVN pre-commit calls process() to init the sniffs
        // and ruleset so there may not be any files to process.
        // But this has to come after that initial setup.
        if (empty($files) === true) {
            return;
        }

        $cliValues    = $this->cli->getCommandLineValues();
        $showProgress = $cliValues['showProgress'];

        if (PHP_CODESNIFFER_VERBOSITY > 0) {
            echo 'Creating file list... ';
        }

        $todo     = $this->getFilesToProcess($files, $local);
        $numFiles = count($todo);

        if (PHP_CODESNIFFER_VERBOSITY > 0) {
            echo "DONE ($numFiles files in queue)".PHP_EOL;
        }

        $numProcessed = 0;
        $dots         = 0;
        $maxLength    = strlen($numFiles);
        $lastDir      = '';
        foreach ($todo as $file) {
            $this->file = $file;
            $currDir = dirname($file);
            if ($lastDir !== $currDir) {
                if (PHP_CODESNIFFER_VERBOSITY > 0) {
                    echo 'Changing into directory '.$currDir.PHP_EOL;
                }

                $lastDir = $currDir;
            }

            $phpcsFile = $this->processFile($file, null, $restrictions);
            $numProcessed++;

            if (PHP_CODESNIFFER_VERBOSITY > 0
                || PHP_CODESNIFFER_INTERACTIVE === true
                || $showProgress === false
            ) {
                continue;
            }

            // Show progress information.
            if ($phpcsFile === null) {
                echo 'S';
            } else {
                $errors   = $phpcsFile->getErrorCount();
                $warnings = $phpcsFile->getWarningCount();
                if ($errors > 0) {
                    echo 'E';
                } else if ($warnings > 0) {
                    echo 'W';
                } else {
                    echo '.';
                }
            }

            $dots++;
            if ($dots === 60) {
                $padding = ($maxLength - strlen($numProcessed));
                echo str_repeat(' ', $padding);
                $percent = round($numProcessed / $numFiles * 100);
                echo " $numProcessed / $numFiles ($percent%)".PHP_EOL;
                $dots = 0;
            }
        }//end foreach

        if (PHP_CODESNIFFER_VERBOSITY === 0
            && PHP_CODESNIFFER_INTERACTIVE === false
            && $showProgress === true
        ) {
            echo PHP_EOL.PHP_EOL;
        }

    }//end process()


    /**
     * Processes a single ruleset and returns a list of the sniffs it represents.
     *
     * Rules founds within the ruleset are processed immediately, but sniff classes
     * are not registered by this method.
     *
     * @param string $rulesetPath The path to a ruleset XML file.
     * @param int    $depth       How many nested processing steps we are in. This
     *                            is only used for debug output.
     *
     * @return array
     * @throws PHP_CodeSniffer_Exception If the ruleset path is invalid.
     */
    public function processRuleset($rulesetPath, $depth=0)
    {
        $rulesetPath = realpath($rulesetPath);
        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            echo str_repeat("\t", $depth);
            echo "Processing ruleset $rulesetPath".PHP_EOL;
        }

        $ruleset = simplexml_load_file($rulesetPath);
        if ($ruleset === false) {
            throw new PHP_CodeSniffer_Exception("Ruleset $rulesetPath is not valid");
        }

        $ownSniffs      = array();
        $includedSniffs = array();
        $excludedSniffs = array();

        $rulesetDir          = dirname($rulesetPath);
        self::$rulesetDirs[] = $rulesetDir;

        if (is_dir($rulesetDir.'/Sniffs') === true) {
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo str_repeat("\t", $depth);
                echo "\tAdding sniff files from \"/.../".basename($rulesetDir)."/Sniffs/\" directory".PHP_EOL;
            }

            $ownSniffs = $this->_expandSniffDirectory($rulesetDir.'/Sniffs', $depth);
        }

        foreach ($ruleset->rule as $rule) {
            if (isset($rule['ref']) === false) {
                continue;
            }

            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo str_repeat("\t", $depth);
                echo "\tProcessing rule \"".$rule['ref'].'"'.PHP_EOL;
            }

            $includedSniffs = array_merge(
                $includedSniffs,
                $this->_expandRulesetReference($rule['ref'], $rulesetDir, $depth)
            );

            if (isset($rule->exclude) === true) {
                foreach ($rule->exclude as $exclude) {
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        echo str_repeat("\t", $depth);
                        echo "\t\tExcluding rule \"".$exclude['name'].'"'.PHP_EOL;
                    }

                    // Check if a single code is being excluded, which is a shortcut
                    // for setting the severity of the message to 0.
                    $parts = explode('.', $exclude['name']);
                    if (count($parts) === 4) {
                        $this->ruleset[(string) $exclude['name']]['severity'] = 0;
                        if (PHP_CODESNIFFER_VERBOSITY > 1) {
                            echo str_repeat("\t", $depth);
                            echo "\t\t=> severity set to 0".PHP_EOL;
                        }
                    } else {
                        $excludedSniffs = array_merge(
                            $excludedSniffs,
                            $this->_expandRulesetReference($exclude['name'], $rulesetDir, ($depth + 1))
                        );
                    }
                }//end foreach
            }//end if

            $this->_processRule($rule, $depth);
        }//end foreach

        // Process custom ignore pattern rules.
        foreach ($ruleset->{'config'} as $config) {
            $this->setConfigData((string) $config['name'], (string) $config['value'], true);
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo str_repeat("\t", $depth);
                echo "\t=> set config value ".(string) $config['name'].': '.(string) $config['value'].PHP_EOL;
            }
        }

        // Process custom ignore pattern rules.
        foreach ($ruleset->{'exclude-pattern'} as $pattern) {
            if (isset($pattern['type']) === false) {
                $pattern['type'] = 'absolute';
            }

            $this->ignorePatterns[(string) $pattern] = (string) $pattern['type'];
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo str_repeat("\t", $depth);
                echo "\t=> added global ".(string) $pattern['type'].' ignore pattern: '.(string) $pattern.PHP_EOL;
            }
        }

        $includedSniffs = array_unique(array_merge($ownSniffs, $includedSniffs));
        $excludedSniffs = array_unique($excludedSniffs);

        if (PHP_CODESNIFFER_VERBOSITY > 1) {
            $included = count($includedSniffs);
            $excluded = count($excludedSniffs);
            echo str_repeat("\t", $depth);
            echo "=> Ruleset processing complete; included $included sniffs and excluded $excluded".PHP_EOL;
        }

        // Merge our own sniff list with our externally included
        // sniff list, but filter out any excluded sniffs.
        $files = array();
        foreach ($includedSniffs as $sniff) {
            if (in_array($sniff, $excludedSniffs) === true) {
                continue;
            } else {
                $files[] = realpath($sniff);
            }
        }

        return $files;

    }//end processRuleset()


    /**
     * Expands a directory into a list of sniff files within.
     *
     * @param string $directory The path to a directory.
     * @param int    $depth     How many nested processing steps we are in. This
     *                          is only used for debug output.
     *
     * @return array
     */
    private function _expandSniffDirectory($directory, $depth=0)
    {
        $sniffs = array();

        if (defined('RecursiveDirectoryIterator::FOLLOW_SYMLINKS') === true) {
            $rdi = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        } else {
            $rdi = new RecursiveDirectoryIterator($directory);
        }

        $di = new RecursiveIteratorIterator($rdi, 0, RecursiveIteratorIterator::CATCH_GET_CHILD);

        foreach ($di as $file) {
            $fileName = $file->getFilename();

            // Skip hidden files.
            if (substr($fileName, 0, 1) === '.') {
                continue;
            }

            // We are only interested in PHP and sniff files.
            $fileParts = explode('.', $fileName);
            if (array_pop($fileParts) !== 'php') {
                continue;
            }

            $basename = basename($fileName, '.php');
            if (substr($basename, -5) !== 'Sniff') {
                continue;
            }

            $path = $file->getPathname();
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo str_repeat("\t", $depth);
                echo "\t\t=> $path".PHP_EOL;
            }

            $sniffs[] = $path;
        }//end foreach

        return $sniffs;

    }//end _expandSniffDirectory()


    /**
     * Expands a ruleset reference into a list of sniff files.
     *
     * @param string $ref        The reference from the ruleset XML file.
     * @param string $rulesetDir The directory of the ruleset XML file, used to
     *                           evaluate relative paths.
     * @param int    $depth      How many nested processing steps we are in. This
     *                           is only used for debug output.
     *
     * @return array
     * @throws PHP_CodeSniffer_Exception If the reference is invalid.
     */
    private function _expandRulesetReference($ref, $rulesetDir, $depth=0)
    {
        // Ignore internal sniffs codes as they are used to only
        // hide and change internal messages.
        if (substr($ref, 0, 9) === 'Internal.') {
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo str_repeat("\t", $depth);
                echo "\t\t* ignoring internal sniff code *".PHP_EOL;
            }

            return array();
        }

        // As sniffs can't begin with a full stop, assume references in
        // this format are relative paths and attempt to convert them
        // to absolute paths. If this fails, let the reference run through
        // the normal checks and have it fail as normal.
        if (substr($ref, 0, 1) === '.') {
            $realpath   = realpath($rulesetDir.'/'.$ref);
            if ($realpath !== false) {
                $ref = $realpath;
                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                    echo str_repeat("\t", $depth);
                    echo "\t\t=> $ref".PHP_EOL;
                }
            }
        }

        if (is_file($ref) === false) {
            // See if this is a whole standard being referenced.
            $path = $this->getInstalledStandardPath($ref);
            if ($path !== null) {
                $ref = $path;
                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                    echo str_repeat("\t", $depth);
                    echo "\t\t=> $ref".PHP_EOL;
                }
            } else if (is_dir($ref) === false) {
                // Work out the sniff path.
                $sepPos = strpos($ref, DIRECTORY_SEPARATOR);
                if ($sepPos !== false) {
                    $stdName = substr($ref, 0, $sepPos);
                    $path    = substr($ref, $sepPos);
                } else {
                    $parts   = explode('.', $ref);
                    $stdName = $parts[0];
                    if (count($parts) === 1) {
                        // A whole standard?
                        $path = '';
                    } else if (count($parts) === 2) {
                        // A directory of sniffs?
                        $path = '/Sniffs/'.$parts[1];
                    } else {
                        // A single sniff?
                        $path = '/Sniffs/'.$parts[1].'/'.$parts[2].'Sniff.php';
                    }
                }

                $newRef  = false;
                $stdPath = $this->getInstalledStandardPath($stdName);
                if ($stdPath !== null && $path !== '') {
                    $newRef = realpath(dirname($stdPath).$path);
                }

                if ($newRef === false) {
                    // The sniff is not locally installed, so check if it is being
                    // referenced as a remote sniff outside the install. We do this
                    // by looking through all directories where we have found ruleset
                    // files before, looking for ones for this particular standard,
                    // and seeing if it is in there.
                    foreach (self::$rulesetDirs as $dir) {
                        if (strtolower(basename($dir)) !== strtolower($stdName)) {
                            continue;
                        }

                        $newRef = realpath($dir.$path);
                        
                        if ($newRef !== false) {
                            $ref = $newRef;
                        }
                    }
                } else {
                    $ref = $newRef;
                }

                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                    echo str_repeat("\t", $depth);
                    echo "\t\t=> $ref".PHP_EOL;
                }
            }//end if
        }//end if

        if (is_dir($ref) === true) {
            if (is_file($ref.'/ruleset.xml') === true) {
                // We are referencing an external coding standard.
                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                    echo str_repeat("\t", $depth);
                    echo "\t\t* rule is referencing a standard using directory name; processing *".PHP_EOL;
                }

                return $this->processRuleset($ref.'/ruleset.xml', ($depth + 2));
            } else {
                // We are referencing a whole directory of sniffs.
                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                    echo str_repeat("\t", $depth);
                    echo "\t\t* rule is referencing a directory of sniffs *".PHP_EOL;
                    echo str_repeat("\t", $depth);
                    echo "\t\tAdding sniff files from directory".PHP_EOL;
                }

                return $this->_expandSniffDirectory($ref, ($depth + 1));
            }
        } else {
            if (is_file($ref) === false) {
                $error = "Referenced sniff \"$ref\" does not exist";
                throw new PHP_CodeSniffer_Exception($error);
            }

            if (substr($ref, -9) === 'Sniff.php') {
                // A single sniff.
                return array($ref);
            } else {
                // Assume an external ruleset.xml file.
                if (PHP_CODESNIFFER_VERBOSITY > 1) {
                    echo str_repeat("\t", $depth);
                    echo "\t\t* rule is referencing a standard using ruleset path; processing *".PHP_EOL;
                }

                return $this->processRuleset($ref, ($depth + 2));
            }
        }//end if

    }//end _expandRulesetReference()


    /**
     * Processes a rule from a ruleset XML file, overriding built-in defaults.
     *
     * @param SimpleXMLElement $rule  The rule object from a ruleset XML file.
     * @param int              $depth How many nested processing steps we are in.
     *                                This is only used for debug output.
     *
     * @return void
     */
    private function _processRule($rule, $depth=0)
    {
        $code = (string) $rule['ref'];

        // Custom severity.
        if (isset($rule->severity) === true) {
            if (isset($this->ruleset[$code]) === false) {
                $this->ruleset[$code] = array();
            }

            $this->ruleset[$code]['severity'] = (int) $rule->severity;
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo str_repeat("\t", $depth);
                echo "\t\t=> severity set to ".(int) $rule->severity.PHP_EOL;
            }
        }

        // Custom message type.
        if (isset($rule->type) === true) {
            if (isset($this->ruleset[$code]) === false) {
                $this->ruleset[$code] = array();
            }

            $this->ruleset[$code]['type'] = (string) $rule->type;
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo str_repeat("\t", $depth);
                echo "\t\t=> message type set to ".(string) $rule->type.PHP_EOL;
            }
        }

        // Custom message.
        if (isset($rule->message) === true) {
            if (isset($this->ruleset[$code]) === false) {
                $this->ruleset[$code] = array();
            }

            $this->ruleset[$code]['message'] = (string) $rule->message;
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo str_repeat("\t", $depth);
                echo "\t\t=> message set to ".(string) $rule->message.PHP_EOL;
            }
        }

        // Custom properties.
        if (isset($rule->properties) === true) {
            foreach ($rule->properties->property as $prop) {
                if (isset($this->ruleset[$code]) === false) {
                    $this->ruleset[$code] = array(
                                             'properties' => array(),
                                            );
                } else if (isset($this->ruleset[$code]['properties']) === false) {
                    $this->ruleset[$code]['properties'] = array();
                }

                $name = (string) $prop['name'];
                if (isset($prop['type']) === true
                    && (string) $prop['type'] === 'array'
                ) {
                    $value = (string) $prop['value'];
                    $this->ruleset[$code]['properties'][$name] = explode(',', $value);
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        echo str_repeat("\t", $depth);
                        echo "\t\t=> array property \"$name\" set to \"$value\"".PHP_EOL;
                    }
                } else {
                    $this->ruleset[$code]['properties'][$name] = (string) $prop['value'];
                    if (PHP_CODESNIFFER_VERBOSITY > 1) {
                        echo str_repeat("\t", $depth);
                        echo "\t\t=> property \"$name\" set to \"".(string) $prop['value'].'"'.PHP_EOL;
                    }
                }
            }//end foreach
        }//end if

        // Ignore patterns.
        foreach ($rule->{'exclude-pattern'} as $pattern) {
            if (isset($this->ignorePatterns[$code]) === false) {
                $this->ignorePatterns[$code] = array();
            }

            if (isset($pattern['type']) === false) {
                $pattern['type'] = 'absolute';
            }

            $this->ignorePatterns[$code][(string) $pattern] = (string) $pattern['type'];
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo str_repeat("\t", $depth);
                echo "\t\t=> added sniff-specific ".(string) $pattern['type'].' ignore pattern: '.(string) $pattern.PHP_EOL;
            }
        }

    }//end _processRule()


    /**
     * Loads and stores sniffs objects used for sniffing files.
     *
     * @param array $files        Paths to the sniff files to register.
     * @param array $restrictions The sniff class names to restrict the allowed
     *                            listeners to.
     *
     * @return void
     */
    public function registerSniffs($files, $restrictions)
    {
        $listeners = array();

        foreach ($files as $file) {
            // Work out where the position of /StandardName/Sniffs/... is
            // so we can determine what the class will be called.
            $sniffPos = strrpos($file, DIRECTORY_SEPARATOR.'Sniffs'.DIRECTORY_SEPARATOR);
            if ($sniffPos === false) {
                continue;
            }

            $slashPos = strrpos(substr($file, 0, $sniffPos), DIRECTORY_SEPARATOR);
            if ($slashPos === false) {
                continue;
            }

            $className = substr($file, ($slashPos + 1));
            $className = substr($className, 0, -4);
            $className = str_replace(DIRECTORY_SEPARATOR, '_', $className);

            // If they have specified a list of sniffs to restrict to, check
            // to see if this sniff is allowed.
            if (empty($restrictions) === false
                && in_array(strtolower($className), $restrictions) === false
            ) {
                continue;
            }

            include_once $file;

            // Support the use of PHP namespaces. If the class name we included
            // contains namespace separators instead of underscores, use this as the
            // class name from now on.
            $classNameNS = str_replace('_', '\\', $className);
            if (class_exists($classNameNS, false) === true) {
                $className = $classNameNS;
            }

            $listeners[$className] = $className;

            if (PHP_CODESNIFFER_VERBOSITY > 2) {
                echo "Registered $className".PHP_EOL;
            }
        }//end foreach

        $this->sniffs = $listeners;

    }//end registerSniffs()


    /**
     * Populates the array of PHP_CodeSniffer_Sniff's for this file.
     *
     * @return void
     * @throws PHP_CodeSniffer_Exception If sniff registration fails.
     */
    public function populateTokenListeners()
    {
        // Construct a list of listeners indexed by token being listened for.
        $this->_tokenListeners = array();

        foreach ($this->sniffs as $listenerClass) {
            // Work out the internal code for this sniff. Detect usage of namespace
            // separators instead of underscores to support PHP namespaces.
            if (strstr($listenerClass, '\\') === false) {
                $parts = explode('_', $listenerClass);
            } else {
                $parts = explode('\\', $listenerClass);
            }

            $code = $parts[0].'.'.$parts[2].'.'.$parts[3];
            $code = substr($code, 0, -5);

            $this->listeners[$listenerClass] = new $listenerClass();

            // Set custom properties.
            if (isset($this->ruleset[$code]['properties']) === true) {
                foreach ($this->ruleset[$code]['properties'] as $name => $value) {
                    $this->setSniffProperty($listenerClass, $name, $value);
                }
            }

            $tokenizers = array('PHP');
            $vars       = get_class_vars($listenerClass);
            if (isset($vars['supportedTokenizers']) === true) {
                $tokenizers = $vars['supportedTokenizers'];
            }

            $tokens = $this->listeners[$listenerClass]->register();
            if (is_array($tokens) === false) {
                $msg = "Sniff $listenerClass register() method must return an array";
                throw new PHP_CodeSniffer_Exception($msg);
            }

            foreach ($tokens as $token) {
                if (isset($this->_tokenListeners[$token]) === false) {
                    $this->_tokenListeners[$token] = array();
                }

                if (in_array($this->listeners[$listenerClass], $this->_tokenListeners[$token], true) === false) {
                    $this->_tokenListeners[$token][] = array(
                                                        'listener'   => $this->listeners[$listenerClass],
                                                        'class'      => $listenerClass,
                                                        'tokenizers' => $tokenizers,
                                                       );
                }
            }
        }//end foreach

    }//end populateTokenListeners()


    /**
     * Set a single property for a sniff.
     *
     * @param string $listenerClass The class name of the sniff.
     * @param string $name          The name of the property to change.
     * @param string $value         The new value of the property.
     *
     * @return void
     */
    public function setSniffProperty($listenerClass, $name, $value)
    {
        // Setting a property for a sniff we are not using.
        if (isset($this->listeners[$listenerClass]) === false) {
            return;
        }

        $name = trim($name);
        if (is_string($value) === true) {
            $value = trim($value);
        }

        // Special case for booleans.
        if ($value === 'true') {
            $value = true;
        } else if ($value === 'false') {
            $value = false;
        }

        $this->listeners[$listenerClass]->$name = $value;

    }//end setSniffProperty()


    /**
     * Get a list of files that will be processed.
     *
     * If passed directories, this method will find all files within them.
     * The method will also perform file extension and ignore pattern filtering.
     *
     * @param string  $paths A list of file or directory paths to process.
     * @param boolean $local If true, only process 1 level of files in directories
     *
     * @return array
     * @throws Exception If there was an error opening a directory.
     * @see    shouldProcessFile()
     */
    public function getFilesToProcess($paths, $local=false)
    {
        $files = array();

        foreach ($paths as $path) {
            if (is_dir($path) === true) {
                if ($local === true) {
                    $di = new DirectoryIterator($path);
                } else {
                    $di = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($path),
                        0,
                        RecursiveIteratorIterator::CATCH_GET_CHILD
                    );
                }

                foreach ($di as $file) {
                    // Check if the file exists after all symlinks are resolved.
                    $filePath = realpath($file->getPathname());
                    if ($filePath === false) {
                        continue;
                    }

                    if (is_dir($filePath) === true) {
                        continue;
                    }

                    if ($this->shouldProcessFile($file->getPathname(), $path) === false) {
                        continue;
                    }

                    $files[] = $file->getPathname();
                }//end foreach
            } else {
                if ($this->shouldIgnoreFile($path, dirname($path)) === true) {
                    continue;
                }

                $files[] = $path;
            }//end if
        }//end foreach

        return $files;

    }//end getFilesToProcess()


    /**
     * Checks filtering rules to see if a file should be checked.
     *
     * Checks both file extension filters and path ignore filters.
     *
     * @param string $path    The path to the file being checked.
     * @param string $basedir The directory to use for relative path checks.
     *
     * @return bool
     */
    public function shouldProcessFile($path, $basedir)
    {
        // Check that the file's extension is one we are checking.
        // We are strict about checking the extension and we don't
        // let files through with no extension or that start with a dot.
        $fileName  = basename($path);
        $fileParts = explode('.', $fileName);
        if ($fileParts[0] === $fileName || $fileParts[0] === '') {
            return false;
        }

        // Checking multi-part file extensions, so need to create a
        // complete extension list and make sure one is allowed.
        $extensions = array();
        array_shift($fileParts);
        foreach ($fileParts as $part) {
            $extensions[implode('.', $fileParts)] = 1;
            array_shift($fileParts);
        }

        $matches = array_intersect_key($extensions, $this->allowedFileExtensions);
        if (empty($matches) === true) {
            return false;
        }

        // If the file's path matches one of our ignore patterns, skip it.
        if ($this->shouldIgnoreFile($path, $basedir) === true) {
            return false;
        }

        return true;

    }//end shouldProcessFile()


    /**
     * Checks filtering rules to see if a file should be ignored.
     *
     * @param string $path    The path to the file being checked.
     * @param string $basedir The directory to use for relative path checks.
     *
     * @return bool
     */
    public function shouldIgnoreFile($path, $basedir)
    {
        $relativePath = $path;
        if (strpos($path, $basedir) === 0) {
            // The +1 cuts off the directory separator as well.
            $relativePath = substr($path, (strlen($basedir) + 1));
        }

        foreach ($this->ignorePatterns as $pattern => $type) {
            if (is_array($pattern) === true) {
                // A sniff specific ignore pattern.
                continue;
            }

            // Maintains backwards compatibility in case the ignore pattern does
            // not have a relative/absolute value.
            if (is_int($pattern) === true) {
                $pattern = $type;
                $type    = 'absolute';
            }

            $replacements = array(
                             '\\,' => ',',
                             '*'   => '.*',
                            );

            // We assume a / directory separator, as do the exclude rules
            // most developers write, so we need a special case for any system
            // that is different.
            if (DIRECTORY_SEPARATOR === '\\') {
                $replacements['/'] = '\\\\';
            }

            $pattern = strtr($pattern, $replacements);

            if ($type === 'relative') {
                $testPath = $relativePath;
            } else {
                $testPath = $path;
            }

            if (preg_match("|{$pattern}|i", $testPath) === 1) {
                return true;
            }
        }//end foreach

        return false;

    }//end shouldIgnoreFile()


    /**
     * Run the code sniffs over a single given file.
     *
     * Processes the file and runs the PHP_CodeSniffer sniffs to verify that it
     * conforms with the standard. Returns the processed file object, or NULL
     * if no file was processed due to error.
     *
     * @param string $file         The file to process.
     * @param string $contents     The contents to parse. If NULL, the content
     *                             is taken from the file system.
     * @param array  $restrictions The sniff codes to restrict the
     *                             violations to.
     *
     * @return PHP_CodeSniffer_File
     * @throws PHP_CodeSniffer_Exception If the file could not be processed.
     * @see    _processFile()
     */
    public function processFile($file, $contents=null, $restrictions=array())
    {
        if ($contents === null && file_exists($file) === false) {
            throw new PHP_CodeSniffer_Exception("Source file $file does not exist");
        }

        $filePath = realpath($file);
        if ($filePath === false) {
            $filePath = $file;
        }

        // Before we go and spend time tokenizing this file, just check
        // to see if there is a tag up top to indicate that the whole
        // file should be ignored. It must be on one of the first two lines.
        $firstContent = $contents;
        if ($contents === null && is_readable($filePath) === true) {
            $handle = fopen($filePath, 'r');
            if ($handle !== false) {
                $firstContent  = fgets($handle);
                $firstContent .= fgets($handle);
                fclose($handle);

                if (strpos($firstContent, '@codingStandardsIgnoreFile') !== false) {
                    // We are ignoring the whole file.
                    if (PHP_CODESNIFFER_VERBOSITY > 0) {
                        echo 'Ignoring '.basename($filePath).PHP_EOL;
                    }

                    return null;
                }
            }
        }//end if

        try {
            $phpcsFile = $this->_processFile($file, $contents, $restrictions);
        } catch (Exception $e) {
            $trace = $e->getTrace();

            $filename = $trace[0]['args'][0];
            if (is_object($filename) === true
                && get_class($filename) === 'PHP_CodeSniffer_File'
            ) {
                $filename = $filename->getFilename();
            } else if (is_numeric($filename) === true) {
                // See if we can find the PHP_CodeSniffer_File object.
                foreach ($trace as $data) {
                    if (isset($data['args'][0]) === true
                        && ($data['args'][0] instanceof PHP_CodeSniffer_File) === true
                    ) {
                        $filename = $data['args'][0]->getFilename();
                    }
                }
            } else if (is_string($filename) === false) {
                $filename = (string) $filename;
            }

            $error = 'An error occurred during processing; checking has been aborted. The error message was: '.$e->getMessage();

            $phpcsFile = new PHP_CodeSniffer_File(
                $filename,
                $this->_tokenListeners,
                $this->allowedFileExtensions,
                $this->ruleset,
                $restrictions,
                $this
            );

            $phpcsFile->addError($error, null);
        }//end try

        $cliValues = $this->cli->getCommandLineValues();

        if (PHP_CODESNIFFER_INTERACTIVE === false) {
            // Cache the report data for this file so we can unset it to save memory.
            $this->reporting->cacheFileReport($phpcsFile, $cliValues);
            return $phpcsFile;
        }

        /*
            Running interactively.
            Print the error report for the current file and then wait for user input.
        */

        // Get current violations and then clear the list to make sure
        // we only print violations for a single file each time.
        $numErrors = null;
        while ($numErrors !== 0) {
            $numErrors = ($phpcsFile->getErrorCount() + $phpcsFile->getWarningCount());
            if ($numErrors === 0) {
                continue;
            }

            $reportClass = $this->reporting->factory('full');
            $reportData  = $this->reporting->prepareFileReport($phpcsFile);
            $reportClass->generateFileReport($reportData, $cliValues['showSources'], $cliValues['reportWidth']);

            echo '<ENTER> to recheck, [s] to skip or [q] to quit : ';
            $input = fgets(STDIN);
            $input = trim($input);

            switch ($input) {
            case 's':
                break(2);
            case 'q':
                exit(0);
                break;
            default:
                // Repopulate the sniffs because some of them save their state
                // and only clear it when the file changes, but we are rechecking
                // the same file.
                $this->populateTokenListeners();
                $phpcsFile = $this->_processFile($file, $contents, $restrictions);
                break;
            }
        }//end while

        return $phpcsFile;

    }//end processFile()


    /**
     * Process the sniffs for a single file.
     *
     * Does raw processing only. No interactive support or error checking.
     *
     * @param string $file         The file to process.
     * @param string $contents     The contents to parse. If NULL, the content
     *                             is taken from the file system.
     * @param array  $restrictions The sniff codes to restrict the
     *                             violations to.
     *
     * @return PHP_CodeSniffer_File
     * @see    processFile()
     */
    private function _processFile($file, $contents, $restrictions)
    {
        if (PHP_CODESNIFFER_VERBOSITY > 0) {
            $startTime = time();
            echo 'Processing '.basename($file).' ';
            if (PHP_CODESNIFFER_VERBOSITY > 1) {
                echo PHP_EOL;
            }
        }

        $phpcsFile = new PHP_CodeSniffer_File(
            $file,
            $this->_tokenListeners,
            $this->allowedFileExtensions,
            $this->ruleset,
            $restrictions,
            $this
        );

        $phpcsFile->start($contents);
        $phpcsFile->cleanUp();

        if (PHP_CODESNIFFER_VERBOSITY > 0) {
            $timeTaken = (time() - $startTime);
            if ($timeTaken === 0) {
                echo 'DONE in < 1 second';
            } else if ($timeTaken === 1) {
                echo 'DONE in 1 second';
            } else {
                echo "DONE in $timeTaken seconds";
            }

            $errors   = $phpcsFile->getErrorCount();
            $warnings = $phpcsFile->getWarningCount();
            echo " ($errors errors, $warnings warnings)".PHP_EOL;
        }

        return $phpcsFile;

    }//end _processFile()


    /**
     * Generates documentation for a coding standard.
     *
     * @param string $standard  The standard to generate docs for
     * @param array  $sniffs    A list of sniffs to limit the docs to.
     * @param string $generator The name of the generator class to use.
     *
     * @return void
     */
    public function generateDocs($standard, array $sniffs=array(), $generator='Text')
    {
        if (class_exists('PHP_CodeSniffer_DocGenerators_'.$generator, true) === false) {
            throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_DocGenerators_'.$generator.' not found');
        }

        $class     = "PHP_CodeSniffer_DocGenerators_$generator";
        $generator = new $class($standard, $sniffs);

        $generator->generate();

    }//end generateDocs()


    /**
     * Gets the array of PHP_CodeSniffer_Sniff's.
     *
     * @return array(PHP_CodeSniffer_Sniff)
     */
    public function getSniffs()
    {
        return $this->listeners;

    }//end getSniffs()


    /**
     * Gets the array of PHP_CodeSniffer_Sniff's indexed by token type.
     *
     * @return array()
     */
    public function getTokenSniffs()
    {
        return $this->_tokenListeners;

    }//end getTokenSniffs()


    /**
     * Takes a token produced from <code>token_get_all()</code> and produces a
     * more uniform token.
     *
     * Note that this method also resolves T_STRING tokens into more discrete
     * types, therefore there is no need to call resolveTstringToken()
     *
     * @param string|array $token The token to convert.
     *
     * @return array The new token.
     */
    public static function standardiseToken($token)
    {
        if (is_array($token) === false) {
            if (isset(self::$_resolveTokenCache[$token]) === true) {
                $newToken = self::$_resolveTokenCache[$token];
            } else {
                $newToken = self::resolveSimpleToken($token);
            }
        } else {
            switch ($token[0]) {
            case T_STRING:
                // Some T_STRING tokens can be more specific.
                $tokenType = strtolower($token[1]);
                if (isset(self::$_resolveTokenCache[$tokenType]) === true) {
                    $newToken = self::$_resolveTokenCache[$tokenType];
                } else {
                    $newToken = self::resolveTstringToken($tokenType);
                }

                break;
            case T_CURLY_OPEN:
                $newToken = array(
                             'code' => T_OPEN_CURLY_BRACKET,
                             'type' => 'T_OPEN_CURLY_BRACKET',
                            );
                break;
            default:
                $newToken = array(
                             'code' => $token[0],
                             'type' => token_name($token[0]),
                            );
                break;
            }//end switch

            $newToken['content'] = $token[1];
        }//end if

        return $newToken;

    }//end standardiseToken()


    /**
     * Converts T_STRING tokens into more usable token names.
     *
     * The token should be produced using the token_get_all() function.
     * Currently, not all T_STRING tokens are converted.
     *
     * @param string $token The T_STRING token to convert as constructed
     *                      by token_get_all().
     *
     * @return array The new token.
     */
    public static function resolveTstringToken($token)
    {
        $newToken = array();
        switch ($token) {
        case 'false':
            $newToken['type'] = 'T_FALSE';
            break;
        case 'true':
            $newToken['type'] = 'T_TRUE';
            break;
        case 'null':
            $newToken['type'] = 'T_NULL';
            break;
        case 'self':
            $newToken['type'] = 'T_SELF';
            break;
        case 'parent':
            $newToken['type'] = 'T_PARENT';
            break;
        default:
            $newToken['type'] = 'T_STRING';
            break;
        }

        $newToken['code'] = constant($newToken['type']);

        self::$_resolveTokenCache[$token] = $newToken;
        return $newToken;

    }//end resolveTstringToken()


    /**
     * Converts simple tokens into a format that conforms to complex tokens
     * produced by token_get_all().
     *
     * Simple tokens are tokens that are not in array form when produced from
     * token_get_all().
     *
     * @param string $token The simple token to convert.
     *
     * @return array The new token in array format.
     */
    public static function resolveSimpleToken($token)
    {
        $newToken = array();

        switch ($token) {
        case '{':
            $newToken['type'] = 'T_OPEN_CURLY_BRACKET';
            break;
        case '}':
            $newToken['type'] = 'T_CLOSE_CURLY_BRACKET';
            break;
        case '[':
            $newToken['type'] = 'T_OPEN_SQUARE_BRACKET';
            break;
        case ']':
            $newToken['type'] = 'T_CLOSE_SQUARE_BRACKET';
            break;
        case '(':
            $newToken['type'] = 'T_OPEN_PARENTHESIS';
            break;
        case ')':
            $newToken['type'] = 'T_CLOSE_PARENTHESIS';
            break;
        case ':':
            $newToken['type'] = 'T_COLON';
            break;
        case '.':
            $newToken['type'] = 'T_STRING_CONCAT';
            break;
        case '?':
            $newToken['type'] = 'T_INLINE_THEN';
            break;
        case ';':
            $newToken['type'] = 'T_SEMICOLON';
            break;
        case '=':
            $newToken['type'] = 'T_EQUAL';
            break;
        case '*':
            $newToken['type'] = 'T_MULTIPLY';
            break;
        case '/':
            $newToken['type'] = 'T_DIVIDE';
            break;
        case '+':
            $newToken['type'] = 'T_PLUS';
            break;
        case '-':
            $newToken['type'] = 'T_MINUS';
            break;
        case '%':
            $newToken['type'] = 'T_MODULUS';
            break;
        case '^':
            $newToken['type'] = 'T_POWER';
            break;
        case '&':
            $newToken['type'] = 'T_BITWISE_AND';
            break;
        case '|':
            $newToken['type'] = 'T_BITWISE_OR';
            break;
        case '<':
            $newToken['type'] = 'T_LESS_THAN';
            break;
        case '>':
            $newToken['type'] = 'T_GREATER_THAN';
            break;
        case '!':
            $newToken['type'] = 'T_BOOLEAN_NOT';
            break;
        case ',':
            $newToken['type'] = 'T_COMMA';
            break;
        case '@':
            $newToken['type'] = 'T_ASPERAND';
            break;
        case '$':
            $newToken['type'] = 'T_DOLLAR';
            break;
        case '`':
            $newToken['type'] = 'T_BACKTICK';
            break;
        default:
            $newToken['type'] = 'T_NONE';
            break;
        }//end switch

        $newToken['code']    = constant($newToken['type']);
        $newToken['content'] = $token;

        self::$_resolveTokenCache[$token] = $newToken;
        return $newToken;

    }//end resolveSimpleToken()


    /**
     * Returns true if the specified string is in the camel caps format.
     *
     * @param string  $string      The string the verify.
     * @param boolean $classFormat If true, check to see if the string is in the
     *                             class format. Class format strings must start
     *                             with a capital letter and contain no
     *                             underscores.
     * @param boolean $public      If true, the first character in the string
     *                             must be an a-z character. If false, the
     *                             character must be an underscore. This
     *                             argument is only applicable if $classFormat
     *                             is false.
     * @param boolean $strict      If true, the string must not have two capital
     *                             letters next to each other. If false, a
     *                             relaxed camel caps policy is used to allow
     *                             for acronyms.
     *
     * @return boolean
     */
    public static function isCamelCaps(
        $string,
        $classFormat=false,
        $public=true,
        $strict=true
    ) {
        // Check the first character first.
        if ($classFormat === false) {
            $legalFirstChar = '';
            if ($public === false) {
                $legalFirstChar = '[_]';
            }

            if ($strict === false) {
                // Can either start with a lowercase letter, or multiple uppercase
                // in a row, representing an acronym.
                $legalFirstChar .= '([A-Z]{2,}|[a-z])';
            } else {
                $legalFirstChar .= '[a-z]';
            }
        } else {
            $legalFirstChar = '[A-Z]';
        }

        if (preg_match("/^$legalFirstChar/", $string) === 0) {
            return false;
        }

        // Check that the name only contains legal characters.
        $legalChars = 'a-zA-Z0-9';
        if (preg_match("|[^$legalChars]|", substr($string, 1)) > 0) {
            return false;
        }

        if ($strict === true) {
            // Check that there are not two capital letters next to each other.
            $length          = strlen($string);
            $lastCharWasCaps = $classFormat;

            for ($i = 1; $i < $length; $i++) {
                $ascii = ord($string{$i});
                if ($ascii >= 48 && $ascii <= 57) {
                    // The character is a number, so it cant be a capital.
                    $isCaps = false;
                } else {
                    if (strtoupper($string{$i}) === $string{$i}) {
                        $isCaps = true;
                    } else {
                        $isCaps = false;
                    }
                }

                if ($isCaps === true && $lastCharWasCaps === true) {
                    return false;
                }

                $lastCharWasCaps = $isCaps;
            }
        }//end if

        return true;

    }//end isCamelCaps()


    /**
     * Returns true if the specified string is in the underscore caps format.
     *
     * @param string $string The string to verify.
     *
     * @return boolean
     */
    public static function isUnderscoreName($string)
    {
        // If there are space in the name, it can't be valid.
        if (strpos($string, ' ') !== false) {
            return false;
        }

        $validName = true;
        $nameBits  = explode('_', $string);

        if (preg_match('|^[A-Z]|', $string) === 0) {
            // Name does not begin with a capital letter.
            $validName = false;
        } else {
            foreach ($nameBits as $bit) {
                if ($bit === '') {
                    continue;
                }

                if ($bit{0} !== strtoupper($bit{0})) {
                    $validName = false;
                    break;
                }
            }
        }

        return $validName;

    }//end isUnderscoreName()


    /**
     * Returns a valid variable type for param/var tag.
     *
     * If type is not one of the standard type, it must be a custom type.
     * Returns the correct type name suggestion if type name is invalid.
     *
     * @param string $varType The variable type to process.
     *
     * @return string
     */
    public static function suggestType($varType)
    {
        if ($varType === '') {
            return '';
        }

        if (in_array($varType, self::$allowedTypes) === true) {
            return $varType;
        } else {
            $lowerVarType = strtolower($varType);
            switch ($lowerVarType) {
            case 'bool':
                return 'boolean';
            case 'double':
            case 'real':
                return 'float';
            case 'int':
                return 'integer';
            case 'array()':
                return 'array';
            }//end switch

            if (strpos($lowerVarType, 'array(') !== false) {
                // Valid array declaration:
                // array, array(type), array(type1 => type2).
                $matches = array();
                $pattern = '/^array\(\s*([^\s^=^>]*)(\s*=>\s*(.*))?\s*\)/i';
                if (preg_match($pattern, $varType, $matches) !== 0) {
                    $type1 = '';
                    if (isset($matches[1]) === true) {
                        $type1 = $matches[1];
                    }

                    $type2 = '';
                    if (isset($matches[3]) === true) {
                        $type2 = $matches[3];
                    }

                    $type1 = self::suggestType($type1);
                    $type2 = self::suggestType($type2);
                    if ($type2 !== '') {
                        $type2 = ' => '.$type2;
                    }

                    return "array($type1$type2)";
                } else {
                    return 'array';
                }//end if
            } else if (in_array($lowerVarType, self::$allowedTypes) === true) {
                // A valid type, but not lower cased.
                return $lowerVarType;
            } else {
                // Must be a custom type name.
                return $varType;
            }//end if
        }//end if

    }//end suggestType()


    /**
     * Get a list of all coding standards installed.
     *
     * Coding standards are directories located in the
     * CodeSniffer/Standards directory. Valid coding standards
     * include a Sniffs subdirectory.
     *
     * @param boolean $includeGeneric If true, the special "Generic"
     *                                coding standard will be included
     *                                if installed.
     * @param string  $standardsDir   A specific directory to look for standards
     *                                in. If not specified, PHP_CodeSniffer will
     *                                look in its default locations.
     *
     * @return array
     * @see isInstalledStandard()
     */
    public static function getInstalledStandards(
        $includeGeneric=false,
        $standardsDir=''
    ) {
        $installedStandards = array();

        if ($standardsDir === '') {
            $installedPaths = array(dirname(__FILE__).'/CodeSniffer/Standards');
            $configPaths    = PHP_CodeSniffer::getConfigData('installed_paths');
            if ($configPaths !== null) {
                $installedPaths = array_merge($installedPaths, explode(',', $configPaths));
            }
        } else {
            $installedPaths = array($standardsDir);
        }

        foreach ($installedPaths as $standardsDir) {
            $di = new DirectoryIterator($standardsDir);
            foreach ($di as $file) {
                if ($file->isDir() === true && $file->isDot() === false) {
                    $filename = $file->getFilename();

                    // Ignore the special "Generic" standard.
                    if ($includeGeneric === false && $filename === 'Generic') {
                        continue;
                    }

                    // Valid coding standard dirs include a standard class.
                    $csFile = $file->getPathname().'/ruleset.xml';
                    if (is_file($csFile) === true) {
                        // We found a coding standard directory.
                        $installedStandards[] = $filename;
                    }
                }
            }
        }//end foreach

        return $installedStandards;

    }//end getInstalledStandards()


    /**
     * Determine if a standard is installed.
     *
     * Coding standards are directories located in the
     * CodeSniffer/Standards directory. Valid coding standards
     * include a ruleset.xml file.
     *
     * @param string $standard The name of the coding standard.
     *
     * @return boolean
     * @see getInstalledStandards()
     */
    public static function isInstalledStandard($standard)
    {
        $path = self::getInstalledStandardPath($standard);
        if ($path !== null) {
            return true;
        } else {
            // This could be a custom standard, installed outside our
            // standards directory.
            $ruleset = rtrim($standard, ' /\\').DIRECTORY_SEPARATOR.'ruleset.xml';
            if (is_file($ruleset) === true) {
                return true;
            }

            // Might also be an actual ruleset file itself.
            // If it has an XML extension, let's at least try it.
            if (is_file($standard) === true
                && substr(strtolower($standard), -4) === '.xml'
            ) {
                return true;
            }
        }

        return false;

    }//end isInstalledStandard()


    /**
     * Return the path of an installed coding standard.
     *
     * Coding standards are directories located in the
     * CodeSniffer/Standards directory. Valid coding standards
     * include a ruleset.xml file.
     *
     * @param string $standard The name of the coding standard.
     *
     * @return string|null
     */
    public static function getInstalledStandardPath($standard)
    {
        $installedPaths = array(dirname(__FILE__).'/CodeSniffer/Standards');
        $configPaths    = PHP_CodeSniffer::getConfigData('installed_paths');
        if ($configPaths !== null) {
            $installedPaths = array_merge($installedPaths, explode(',', $configPaths));
        }

        foreach ($installedPaths as $installedPath) {
            $path = realpath($installedPath.'/'.$standard.'/ruleset.xml');
            if (is_file($path) === true) {
                return $path;
            }
        }

        return null;

    }//end getInstalledStandardPath()


    /**
     * Get a single config value.
     *
     * Config data is stored in the data dir, in a file called
     * CodeSniffer.conf. It is a simple PHP array.
     *
     * @param string $key The name of the config value.
     *
     * @return string|null
     * @see setConfigData()
     * @see getAllConfigData()
     */
    public static function getConfigData($key)
    {
        $phpCodeSnifferConfig = self::getAllConfigData();

        if ($phpCodeSnifferConfig === null) {
            return null;
        }

        if (isset($phpCodeSnifferConfig[$key]) === false) {
            return null;
        }

        return $phpCodeSnifferConfig[$key];

    }//end getConfigData()


    /**
     * Set a single config value.
     *
     * Config data is stored in the data dir, in a file called
     * CodeSniffer.conf. It is a simple PHP array.
     *
     * @param string      $key   The name of the config value.
     * @param string|null $value The value to set. If null, the config
     *                           entry is deleted, reverting it to the
     *                           default value.
     * @param boolean     $temp  Set this config data temporarily for this
     *                           script run. This will not write the config
     *                           data to the config file.
     *
     * @return boolean
     * @see getConfigData()
     * @throws PHP_CodeSniffer_Exception If the config file can not be written.
     */
    public static function setConfigData($key, $value, $temp=false)
    {
        if ($temp === false) {
            $configFile = dirname(__FILE__).'/CodeSniffer.conf';
            if (is_file($configFile) === false
                && strpos('@data_dir@', '@data_dir') === false
            ) {
                // If data_dir was replaced, this is a PEAR install and we can
                // use the PEAR data dir to store the conf file.
                $configFile = '@data_dir@/PHP_CodeSniffer/CodeSniffer.conf';
            }

            if (is_file($configFile) === true
                && is_writable($configFile) === false
            ) {
                $error = "Config file $configFile is not writable";
                throw new PHP_CodeSniffer_Exception($error);
            }
        }

        $phpCodeSnifferConfig = self::getAllConfigData();

        if ($value === null) {
            if (isset($phpCodeSnifferConfig[$key]) === true) {
                unset($phpCodeSnifferConfig[$key]);
            }
        } else {
            $phpCodeSnifferConfig[$key] = $value;
        }

        if ($temp === false) {
            $output  = '<'.'?php'."\n".' $phpCodeSnifferConfig = ';
            $output .= var_export($phpCodeSnifferConfig, true);
            $output .= "\n?".'>';

            if (file_put_contents($configFile, $output) === false) {
                return false;
            }
        }

        $GLOBALS['PHP_CODESNIFFER_CONFIG_DATA'] = $phpCodeSnifferConfig;

        return true;

    }//end setConfigData()


    /**
     * Get all config data in an array.
     *
     * @return string
     * @see getConfigData()
     */
    public static function getAllConfigData()
    {
        if (isset($GLOBALS['PHP_CODESNIFFER_CONFIG_DATA']) === true) {
            return $GLOBALS['PHP_CODESNIFFER_CONFIG_DATA'];
        }

        $configFile = dirname(__FILE__).'/CodeSniffer.conf';
        if (is_file($configFile) === false) {
            $configFile = '@data_dir@/PHP_CodeSniffer/CodeSniffer.conf';
        }

        if (is_file($configFile) === false) {
            return null;
        }

        include $configFile;
        $GLOBALS['PHP_CODESNIFFER_CONFIG_DATA'] = $phpCodeSnifferConfig;
        return $GLOBALS['PHP_CODESNIFFER_CONFIG_DATA'];

    }//end getAllConfigData()


}//end class

?>
