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

namespace Magento\Customer\Test\Block\Address;

use Magento\Customer\Test\Fixture\AddressInjectable;

/**
 * Class Renderer
 * Render output from AddressInjectable fixture according to data format type
 */
class Renderer
{
    /**
     * Address format type
     *
     * @var string
     */
    protected $type;

    /**
     * AddressInjectable fixture
     *
     * @var AddressInjectable
     */
    protected $address;

    /**
     * @param AddressInjectable $address
     * @param string $type
     */
    public function __construct(AddressInjectable $address, $type = null)
    {
        $this->address = $address;
        $this->type = $type;
    }

    /**
     * Returns pattern according to address type
     *
     * @return string
     */
    protected function getPattern()
    {
        $region = $this->resolveRegion();
        switch ($this->type) {
            case "oneline":
            default:
                $outputPattern = "{{depend}}{{prefix}} {{/depend}}{{firstname}} {{depend}}{{middlename}} {{/depend}}"
                    . "{{lastname}}{{depend}} {{suffix}}{{/depend}}, {{street}}, "
                    . "{{city}}, {{{$region}}} {{postcode}}, {{country_id}}";
                break;
        }
        return $outputPattern;
    }

    /**
     * Render address according to format type
     *
     * @return string
     */
    public function render()
    {
        $outputPattern = $this->getPattern();
        $fields = $this->getFieldsArray($outputPattern);
        $output = $this->preparePattern();

        foreach ($fields as $field) {
            $data = $this->address->getData($field);
            $output = str_replace($field, $data, $output);
        }

        $output = str_replace(['{', '}'], '', $output);
        return $output;
    }

    /**
     * Get an array of necessary fields from pattern
     *
     * @param string $outputPattern
     * @return mixed
     */
    protected function getFieldsArray($outputPattern)
    {
        $fieldsArray = [];
        preg_match_all('@\{\{(\w+)\}\}@', $outputPattern, $matches);
        foreach ($matches[1] as $item) {
            if ($item != 'depend') {
                $fieldsArray[] = $item;
            }
        }
        return $fieldsArray;
    }

    /**
     * Purge fields from pattern which are not present in fixture
     *
     * @return string
     */
    protected function preparePattern()
    {
        $outputPattern = $this->getPattern();
        preg_match_all('@\{\{depend\}\}(.*?)\{\{.depend\}\}@', $outputPattern, $matches);
        foreach ($matches[1] as $key => $dependPart) {
            preg_match_all('@\{\{(\w+)\}\}@', $dependPart, $depends);
            foreach ($depends[1] as $depend) {
                if ($this->address->getData(trim($depend)) === null) {
                    $outputPattern = str_replace($matches[0][$key], "", $outputPattern);
                }
            }
        }
        return $outputPattern;
    }

    /**
     * Check necessary field to retrieve according to address country
     *
     * @return string
     */
    protected function resolveRegion()
    {
        return $this->address->hasData('region') ? 'region' : 'region_id';
    }
}
