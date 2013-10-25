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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Decorator that filters out layout files that belong to modules, output of which is prohibited
 */
namespace Magento\Core\Model\Layout\File\Source\Decorator;

class ModuleOutput implements \Magento\Core\Model\Layout\File\SourceInterface
{
    /**
     * @var \Magento\Core\Model\Layout\File\SourceInterface
     */
    private $_subject;

    /**
     * @var \Magento\Core\Model\ModuleManager
     */
    private $_moduleManager;

    /**
     * @param \Magento\Core\Model\Layout\File\SourceInterface $subject
     * @param \Magento\Core\Model\ModuleManager $moduleManager
     */
    public function __construct(
        \Magento\Core\Model\Layout\File\SourceInterface $subject,
        \Magento\Core\Model\ModuleManager $moduleManager
    ) {
        $this->_subject = $subject;
        $this->_moduleManager = $moduleManager;
    }

    /**
     * Filter out theme files that belong to inactive modules or ones explicitly configured to not produce any output
     *
     * {@inheritdoc}
     */
    public function getFiles(\Magento\View\Design\ThemeInterface $theme)
    {
        $result = array();
        foreach ($this->_subject->getFiles($theme) as $file) {
            if ($this->_moduleManager->isOutputEnabled($file->getModule())) {
                $result[] = $file;
            }
        }
        return $result;
    }
}
