<?php
/**
 * Factory is used to hide the details of how a Twig Environment is built.
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
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\TemplateEngine\Twig;

class EnvironmentFactory
{
    /**
     * @var \Magento\Core\Model\TemplateEngine\Twig\Extension
     */
    protected $_extension;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;
    
    /**
     * @var \Twig_Environment
     */
    private $_environment;

    /**
     * @var \Magento\Core\Model\Dir
     */
    private $_dir;
    
    /**
     * @var \Magento\Core\Model\Logger
     */
    private $_logger;

    /**
     * @var \Twig_LoaderInterface
     */
    private $_loader;

    /**
     * Create new instance of factory
     *
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Core\Model\TemplateEngine\Twig\Extension $extension
     * @param \Magento\Core\Model\Dir $dir
     * @param \Magento\Core\Model\Logger $logger
     * @param \Twig_LoaderInterface $loader
     */
    public function __construct(
        \Magento\Filesystem $filesystem,
        \Magento\Core\Model\TemplateEngine\Twig\Extension $extension,
        \Magento\Core\Model\Dir $dir,
        \Magento\Core\Model\Logger $logger,
        \Twig_LoaderInterface $loader
    ) {
        $this->_filesystem = $filesystem;
        $this->_extension = $extension;
        $this->_dir = $dir;
        $this->_logger = $logger;
        $this->_loader = $loader;
        $this->_environment = null;
    }

    /**
     * Initialize (if necessary) and return the Twig environment.
     *
     * @return \Twig_Environment
     */
    public function create()
    {
        if ($this->_environment === null) {
            $this->_environment = new \Twig_Environment($this->_loader);
            try {
                $precompiledTmpltDir = $this->_dir->getDir(\Magento\Core\Model\Dir::VAR_DIR) . '/twig_templates';
                $this->_filesystem->createDirectory($precompiledTmpltDir);
                $this->_environment->setCache($precompiledTmpltDir);
            } catch (\Magento\Filesystem\FilesystemException $e) {
                // Twig will just run slowly but not worth stopping Magento for it
                $this->_logger->logException($e);
            } catch (\InvalidArgumentException $e) {
                // Can happen if path isn't found, shouldn't stop Magento
                $this->_logger->logException($e);
            }
            $this->_environment->enableStrictVariables();
            $this->_environment->addExtension(new \Twig_Extension_Escaper('html'));
            $this->_environment->addExtension(new \Twig_Extension_Optimizer(1));
            $this->_environment->addExtension($this->_extension);
            $this->_environmentInitialized = true;
        }
        return $this->_environment;
    }
}
