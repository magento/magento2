<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Date;

use IntlDateFormatter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\ResolverInterface;

/**
 * @api
 * @since 100.0.2
 */
class Short implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var IntlDateFormatter
     */
    private $dateFormatter;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        $arr[] = ['label' => '', 'value' => ''];
        $arr[] = ['label' => 'MM/DD/YY ' . $this->getTimeFormat(time(), '(M/d/y)'), 'value' => 'M/d/y'];
        $arr[] = ['label' => 'MM/DD/YYYY '. $this->getTimeFormat(time(), '(M/d/Y)'), 'value' => 'M/d/Y'];
        $arr[] = ['label' => 'DD/MM/YY ' . $this->getTimeFormat(time(), '(d/m/y)'), 'value' => 'd/M/y'];
        $arr[] = ['label' => 'DD/MM/YYYY ' . $this->getTimeFormat(time(), '(d/m/Y)'), 'value' => 'd/M/Y'];
        return $arr;
    }

    /**
     * This method format timestamp value.
     *
     * @param int $datetime
     * @param string $format
     *
     * @return string
     */
    private function getTimeFormat(int $datetime, string $format = 'Y/M/d'): string
    {
        if (!$this->dateFormatter) {
            $localeResolver = ObjectManager::getInstance()->get(ResolverInterface::class);
            $this->dateFormatter = new \IntlDateFormatter(
                $localeResolver->getLocale(),
                IntlDateFormatter::SHORT,
                IntlDateFormatter::SHORT
            );
        }
        $this->dateFormatter->setPattern($format);

        return $this->dateFormatter->format($datetime);
    }
}
