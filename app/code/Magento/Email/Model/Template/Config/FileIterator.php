<?php
/**
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
namespace Magento\Email\Model\Template\Config;

/**
 * Class FileIterator
 */
class FileIterator extends \Magento\Framework\Config\FileIterator
{
    /**
     * @var \Magento\Framework\Module\Dir\ReverseResolver
     */
    protected $_moduleDirResolver;

    /**
     * @param \Magento\Framework\Filesystem\Directory\ReadInterface $directory
     * @param array $paths
     * @param \Magento\Framework\Module\Dir\ReverseResolver $dirResolver
     */
    public function __construct(
        \Magento\Framework\Filesystem\Directory\ReadInterface $directory,
        array $paths,
        \Magento\Framework\Module\Dir\ReverseResolver $dirResolver
    ) {
        parent::__construct($directory, $paths);
        $this->_moduleDirResolver = $dirResolver;
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function current()
    {
        $path = $this->directoryRead->getAbsolutePath($this->key());
        $moduleName = $this->_moduleDirResolver->getModuleName($path);
        if (!$moduleName) {
            throw new \UnexpectedValueException(
                sprintf("Unable to determine a module, file '%s' belongs to.", $this->key())
            );
        }
        $contents = $this->directoryRead->readFile($this->key());
        return str_replace('<template ', '<template module="' . $moduleName . '" ', $contents);
    }
}
