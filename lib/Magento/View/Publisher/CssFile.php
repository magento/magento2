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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\View\Publisher;

/**
 * Publisher file type CSS
 */
class CssFile extends FileAbstract
{
    /**
     * Determine whether a file needs to be published
     *
     * If sourcePath points to CSS file and developer mode is enabled - publish file
     *
     * @return bool
     */
    public function isPublicationAllowed()
    {
        if ($this->isPublicationAllowed === null) {
            $filePath = str_replace('\\', '/', $this->sourcePath);

            if ($this->isLibFile($filePath)) {
                $this->isPublicationAllowed = false;
            } elseif (!$this->isViewStaticFile($filePath)) {
                $this->isPublicationAllowed = true;
            } else {
                $this->isPublicationAllowed = $this->viewService->getAppMode() === \Magento\App\State::MODE_DEVELOPER;
            }
        }
        return $this->isPublicationAllowed;
    }

    /**
     * Build unique file path for publication
     *
     * @return string
     */
    public function buildUniquePath()
    {
        return $this->buildPublicViewRedundantFilename();
    }
}
