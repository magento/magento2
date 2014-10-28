<?php
/**
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\I18n\Code\Dictionary\Options;

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
                $this->options = array(
                    array(
                        'type' => 'php',
                        'paths' => array($this->directory . '/app/code/', $this->directory . '/app/design/'),
                        'fileMask' => '/\.(php|phtml)$/'
                    ),
                    array(
                        'type' => 'js',
                        'paths' => array(
                            $this->directory . '/app/code/',
                            $this->directory . '/app/design/',
                            $this->directory . '/lib/web/mage/',
                            $this->directory . '/lib/web/varien/'
                        ),
                        'fileMask' => '/\.(js|phtml)$/'
                    ),
                    array(
                        'type' => 'xml',
                        'paths' => array($this->directory . '/app/code/', $this->directory . '/app/design/'),
                        'fileMask' => '/\.xml$/'
                    )
                );
            } else {
                $this->options = array(
                    array('type' => 'php', 'paths' => array($this->directory), 'fileMask' => '/\.(php|phtml)$/'),
                    array('type' => 'js', 'paths' => array($this->directory), 'fileMask' => '/\.(js|phtml)$/'),
                    array('type' => 'xml', 'paths' => array($this->directory), 'fileMask' => '/\.xml$/')
                );
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
