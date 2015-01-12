<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\I18n\Dictionary\Options;

/**
 * Dictionary generator options resolver
 */
class Resolver implements ResolverInterface
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var bool
     */
    protected $withContext;

    /**
     * Resolver construct
     *
     * @param string $directory
     * @param bool $withContext
     */
    public function __construct($directory, $withContext)
    {
        $this->directory = $directory;
        $this->withContext = $withContext;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (null === $this->options) {
            if ($this->withContext) {
                $this->directory = rtrim($this->directory, '\\/');
                $this->options = [
                    [
                        'type' => 'php',
                        'paths' => [$this->directory . '/app/code/', $this->directory . '/app/design/'],
                        'fileMask' => '/\.(php|phtml)$/',
                    ],
                    [
                        'type' => 'js',
                        'paths' => [
                            $this->directory . '/app/code/',
                            $this->directory . '/app/design/',
                            $this->directory . '/lib/web/mage/',
                            $this->directory . '/lib/web/varien/',
                        ],
                        'fileMask' => '/\.(js|phtml)$/'
                    ],
                    [
                        'type' => 'xml',
                        'paths' => [$this->directory . '/app/code/', $this->directory . '/app/design/'],
                        'fileMask' => '/\.xml$/'
                    ],
                ];
            } else {
                $this->options = [
                    ['type' => 'php', 'paths' => [$this->directory], 'fileMask' => '/\.(php|phtml)$/'],
                    ['type' => 'js', 'paths' => [$this->directory], 'fileMask' => '/\.(js|phtml)$/'],
                    ['type' => 'xml', 'paths' => [$this->directory], 'fileMask' => '/\.xml$/'],
                ];
            }
            foreach ($this->options as $option) {
                $this->isValidPaths($option['paths']);
            }
        }
        return $this->options;
    }

    /**
     * @param array $directories
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function isValidPaths($directories)
    {
        foreach ($directories as $path) {
            if (!is_dir($path)) {
                if ($this->withContext) {
                    throw new \InvalidArgumentException('Specified path is not a Magento root directory');
                } else {
                    throw new \InvalidArgumentException('Specified path doesn\'t exist');
                }
            }
        }
    }
}
