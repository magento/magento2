<?php
/**
 * Loads email template configuration from multiple XML files by merging them together
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Email\Template\Config;

class Reader extends \Magento\Config\Reader\Filesystem
{
    /**
     * @var \Magento\App\Module\Dir\ReverseResolver
     */
    private $_moduleDirResolver;

    /**
     * @param \Magento\Config\FileResolverInterface $fileResolver
     * @param \Magento\Core\Model\Email\Template\Config\Converter $converter
     * @param \Magento\Core\Model\Email\Template\Config\SchemaLocator $schemaLocator
     * @param \Magento\Config\ValidationStateInterface $validationState
     * @param \Magento\App\Module\Dir\ReverseResolver $moduleDirResolver
     */
    public function __construct(
        \Magento\Config\FileResolverInterface $fileResolver,
        \Magento\Core\Model\Email\Template\Config\Converter $converter,
        \Magento\Core\Model\Email\Template\Config\SchemaLocator $schemaLocator,
        \Magento\Config\ValidationStateInterface $validationState,
        \Magento\App\Module\Dir\ReverseResolver $moduleDirResolver
    ) {
        $fileName = 'email_templates.xml';
        $idAttributes = array(
            '/config/template' => 'id',
        );
        parent::__construct($fileResolver, $converter, $schemaLocator, $validationState, $fileName, $idAttributes);
        $this->_moduleDirResolver = $moduleDirResolver;
    }

    /**
     * Add information on context of a module, config file belongs to
     *
     * {@inheritdoc}
     * @throws \UnexpectedValueException
     */
    protected function _readFileContents($filename)
    {
        $result = parent::_readFileContents($filename);
        $moduleName = $this->_moduleDirResolver->getModuleName($filename);
        if (!$moduleName) {
            throw new \UnexpectedValueException("Unable to determine a module, file '$filename' belongs to.");
        }
        $result = str_replace('<template ', '<template module="' . $moduleName . '" ', $result);
        return $result;
    }
}
