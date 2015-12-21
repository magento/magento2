<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Block\Adminhtml;

/**
 * Is used to represent a new unified form with collapsible sections on the page.
 */
class FormSections extends AbstractFormContainers
{
    /**
     * @param string $sectionName
     * @return Section
     * @throws \Exception
     */
    protected function getSection($sectionName)
    {
        return $this->getContainer($sectionName);
    }

    /**
     * {@inheritdoc}
     */
    protected function openContainer($sectionName)
    {
        return $this->openSection($sectionName);
    }

    /**
     * Opens the section.
     *
     * @param string $sectionName
     * @return $this
     */
    public function openSection($sectionName)
    {
        //TODO: needs to be implemented when ready
        $this->browser->find($this->header)->hover();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function isContainerVisible($containerName)
    {
        return true;
    }
}
