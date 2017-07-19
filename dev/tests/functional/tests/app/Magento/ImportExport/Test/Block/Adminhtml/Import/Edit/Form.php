<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Block\Adminhtml\Import\Edit;

use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Import form.
 */
class Form extends \Magento\Mtf\Block\Form
{
    /**
     * Fill import form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $data = $fixture->getData();

        //Move file to the end of the list in order to fill form with attached file correctly.
        $importFile = $data['import_file'];
        unset($data['import_file']);
        $data['import_file'] = $importFile;

        $fields = isset($data['fields']) ? $data['fields'] : $data;
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping, $element);

        return $this;
    }
}
