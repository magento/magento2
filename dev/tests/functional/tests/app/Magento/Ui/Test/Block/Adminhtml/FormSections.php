<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Block\Adminhtml;

use Magento\Ui\Test\Block\Adminhtml\AbstractFormContainers;

/**
 * Is used to represent a new unified form with collapsible sections on the page.
 */
class FormSections extends AbstractFormContainers
{
    /**
     * @param string $sectionName
     * @return $this
     */
    protected function openContainer($sectionName)
    {
        $this->browser->find($this->header)->hover();
        return $this;
    }

    /**
     * Open section.
     *
     * @param string $sectionName
     * @return $this
     */
    public function openSection($sectionName)
    {
        return $this->openContainer($sectionName);
    }

    /**
     * @param string $sectionName
     * @return $this
     * @throws \Exception
     */
    public function getSection($sectionName)
    {
        return $this->getContainer($sectionName);
    }

    /**
     * Check whether section is visible.
     *
     * @param string $containerName
     * @return bool
     */
    protected function isContainerVisible($containerName)
    {
        return true;
    }
}
