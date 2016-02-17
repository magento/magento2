<?php

namespace League\CLImate\TerminalObject\Basic;

class Draw extends BasicTerminalObject
{
    /**
	 * The directories we should be looking for art in
	 *
	 * @var array $art_dirs
	 */
    protected $art_dirs = [];

    /**
	 * The default art if we can't find what the user requested
	 *
	 * @var string $default_art
	 */
    protected $default_art = '404';

    /**
	 * The art requested by the user
	 *
	 * @var string $art
	 */
    protected $art = '';

    public function __construct($art)
    {
        // Add the default art directory
        $this->addDir(__DIR__ . '/../../ASCII');

        $this->art = $art;
    }

    /**
	 * Specify which settings Draw needs to import
	 *
	 * @return array
	 */
    public function settings()
    {
        return ['Art'];
    }

    /**
	 * Import the Art setting (any directories the user added)
	 *
	 * @param array $setting
	 */
    public function importSettingArt($setting)
    {
        foreach ($setting->dirs as $dir) {
            $this->addDir($dir);
        }
    }

    /**
	 * Add a directory to search for art in
	 *
	 * @param string $dir
	 */
    protected function addDir($dir)
    {
        // Add any additional directories to the top of the array
        // so that the user can override art
        array_unshift($this->art_dirs, rtrim($dir, '/'));

        // Keep the array clean
        $this->art_dirs = array_unique($this->art_dirs);
        $this->art_dirs = array_filter($this->art_dirs);
        $this->art_dirs = array_values($this->art_dirs);
    }

    /**
	 * Find a valid art path
	 *
	 * @param string $art
     *
	 * @return string
	 */
    protected function path($art)
    {
        foreach ($this->art_dirs as $dir) {
            // Look for anything that has the $art filename
            $paths  = glob($dir . '/' . $art . '.*');

            // If we've got one, no need to look any further
            if (!empty($paths)) {
                break;
            }
        }

        return reset($paths);
    }

    /**
	 * Parse the contents of the file and return each line
	 *
	 * @param string $path
     *
	 * @return array
	 */
    protected function parse($path)
    {
        $output = file_get_contents($path);
        $output = explode("\n", $output);
        $output = array_map('rtrim', $output);

        return $output;
    }

    /**
	 * Return the art
	 *
	 * @return array
	 */
    public function result()
    {
        $file = $this->path($this->art) ?: $this->path($this->default_art);

        return $this->parse($file);
    }
}
