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

namespace Magento\Customer\Test\Fixture\CustomerGroup;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Magento\Tax\Test\Fixture\TaxClass;

/**
 * Class TaxClassIds
 *
 * Data keys:
 *  - dataSet
 */
class TaxClassIds implements FixtureInterface
{
    /**
     * Tax class name
     *
     * @var string
     */
    protected $data;

    /**
     * TaxClass fixture
     *
     * @var TaxClass
     */
    protected $taxClass;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        array $params,
        array $data
    ) {
        $this->params = $params;
        if (isset($data['dataSet']) && $data['dataSet'] !== '-') {
            $dataSet = $data['dataSet'];
            /** @var \Magento\Tax\Test\Fixture\TaxClass $taxClass */
            $taxClass = $fixtureFactory->createByCode('taxClass', ['dataSet' => $dataSet]);
            if (!$taxClass->hasData('id')) {
                $taxClass->persist();
            }
            $this->data = $taxClass->getClassName();
            $this->taxClass = $taxClass;
        }
    }

    /**
     * Persist custom selections products
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param $key [optional]
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return TaxClass fixture
     *
     * @return TaxClass
     */
    public function getTaxClass()
    {
        return $this->taxClass;
    }

    /**
     * Return data set configuration settings
     *
     * @return string
     */
    public function getDataConfig()
    {
        return $this->params;
    }
}
