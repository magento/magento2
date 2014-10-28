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

namespace Magento\CatalogSearch\Test\Block\Advanced;

use Mtf\Client\Element;
use Mtf\Fixture\FixtureInterface;
use Mtf\Block\Form as ParentForm;

/**
 * Class Form
 * Advanced search form
 */
class Form extends ParentForm
{
    /**
     * Search button selector
     *
     * @var string
     */
    protected $searchButtonSelector = '.action.search';

    /**
     * Field selector select tax class
     *
     * @var string
     */
    protected $taxClassSelector = '#tax_class_id';

    /**
     * Submit search form
     *
     * @return void
     */
    public function submit()
    {
        $this->_rootElement->find($this->searchButtonSelector)->click();
    }

    /**
     * Fill the root form
     *
     * @param FixtureInterface $fixture
     * @param Element|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, Element $element = null)
    {
        // Prepare price data
        $data = $fixture->getData();
        if (isset($data['price'])) {
            $data = array_merge($data, $data['price']);
            unset($data['price']);
        }

        // Mapping
        $mapping = $this->dataMapping($data);
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Fill form with custom fields
     * (for End To End Tests)
     *
     * @param FixtureInterface $fixture
     * @param array $fields
     * @param Element $element
     */
    public function fillCustom(FixtureInterface $fixture, array $fields, Element $element = null)
    {
        $data = $fixture->getData('fields');
        $dataForMapping = array_intersect_key($data, array_flip($fields));
        $mapping = $this->dataMapping($dataForMapping);
        $this->_fill($mapping, $element);
    }
}
