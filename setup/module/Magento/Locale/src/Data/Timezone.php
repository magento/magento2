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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Locale\Data;

class Timezone implements ListInterface
{
    /**
     * @var array
     */
    protected $data = [
        'Australia/Darwin' => 'AUS Central Standard Time',
        'Australia/Sydney' => 'AUS Eastern Standard Time',
        'Asia/Kabul' => 'Afghanistan Standard Time',
        'America/Anchorage' => 'Alaskan Standard Time',
        'Asia/Riyadh' => 'Arab Standard Time',
        'Asia/Dubai' => 'Arabian Standard Time',
        'Asia/Baghdad' => 'Arabic Standard Time',
        'America/Buenos_Aires' => 'Argentina Standard Time',
        'Asia/Yerevan' => 'Armenian Standard Time',
        'America/Halifax' => 'Atlantic Standard Time',
        'Asia/Baku' => 'Azerbaijan Standard Time',
        'Atlantic/Azores' => 'Azores Standard Time',
        'America/Regina' => 'Canada Central Standard Time',
        'Atlantic/Cape_Verde' => 'Cape Verde Standard Time',
        'Australia/Adelaide' => 'Cen. Australia Standard Time',
        'America/Guatemala' => 'Central America Standard Time',
        'Asia/Dhaka' => 'Central Asia Standard Time',
        'America/Manaus' => 'Central Brazilian Standard Time',
        'Europe/Budapest' => 'Central Europe Standard Time',
        'Europe/Warsaw' => 'Central European Standard Time',
        'Pacific/Guadalcanal' => 'Central Pacific Standard Time',
        'America/Chicago' => 'Central Standard Time',
        'America/Mexico_City' => 'Central Standard Time (Mexico)',
        'Asia/Shanghai' => 'China Standard Time',
        'Etc/GMT+12' => 'Dateline Standard Time',
        'Africa/Nairobi' => 'E. Africa Standard Time',
        'Australia/Brisbane' => 'E. Australia Standard Time',
        'Europe/Minsk' => 'E. Europe Standard Time',
        'America/Sao_Paulo' => 'E. South America Standard Time',
        'America/New_York' => 'Eastern Standard Time',
        'Africa/Cairo' => 'Egypt Standard Time',
        'Asia/Yekaterinburg' => 'Ekaterinburg Standard Time',
        'Europe/Kiev' => 'FLE Standard Time',
        'Pacific/Fiji' => 'Fiji Standard Time',
        'Europe/London' => 'GMT Standard Time',
        'Europe/Istanbul' => 'GTB Standard Time',
        'Etc/GMT-3' => 'Georgian Standard Time',
        'America/Godthab' => 'Greenland Standard Time',
        'Atlantic/Reykjavik' => 'Greenwich Standard Time',
        'Pacific/Honolulu' => 'Hawaiian Standard Time',
        'Asia/Calcutta' => 'India Standard Time',
        'Asia/Tehran' => 'Iran Standard Time',
        'Asia/Jerusalem' => 'Israel Standard Time',
        'Asia/Amman' => 'Jordan Standard Time',
        'Asia/Seoul' => 'Korea Standard Time',
        'Indian/Mauritius' => 'Mauritius Standard Time',
        'America/Chihuahua' => 'Mexico Standard Time 2',
        'Atlantic/South_Georgia' => 'Mid-Atlantic Standard Time',
        'Asia/Beirut' => 'Middle East Standard Time',
        'America/Montevideo' => 'Montevideo Standard Time',
        'Africa/Casablanca' => 'Morocco Standard Time',
        'America/Denver' => 'Mountain Standard Time',
        'Asia/Rangoon' => 'Myanmar Standard Time',
        'Asia/Novosibirsk' => 'N. Central Asia Standard Time',
        'Africa/Windhoek' => 'Namibia Standard Time',
        'Asia/Katmandu' => 'Nepal Standard Time',
        'Pacific/Auckland' => 'New Zealand Standard Time',
        'America/St_Johns' => 'Newfoundland Standard Time',
        'Asia/Irkutsk' => 'North Asia East Standard Time',
        'Asia/Krasnoyarsk' => 'North Asia Standard Time',
        'America/Santiago' => 'Pacific SA Standard Time',
        'America/Los_Angeles' => 'Pacific Standard Time',
        'America/Tijuana' => 'Pacific Standard Time (Mexico)',
        'Asia/Karachi' => 'Pakistan Standard Time',
        'Europe/Paris' => 'Romance Standard Time',
        'Europe/Moscow' => 'Russian Standard Time',
        'Etc/GMT+3' => 'SA Eastern Standard Time',
        'America/Bogota' => 'SA Pacific Standard Time',
        'America/La_Paz' => 'SA Western Standard Time',
        'Asia/Bangkok' => 'SE Asia Standard Time',
        'Pacific/Apia' => 'Samoa Standard Time',
        'Asia/Singapore' => 'Singapore Standard Time',
        'Africa/Johannesburg' => 'South Africa Standard Time',
        'Asia/Colombo' => 'Sri Lanka Standard Time',
        'Asia/Taipei' => 'Taipei Standard Time',
        'Australia/Hobart' => 'Tasmania Standard Time',
        'Asia/Tokyo' => 'Tokyo Standard Time',
        'Pacific/Tongatapu' => 'Tonga Standard Time',
        'Etc/GMT+5' => 'US Eastern Standard Time',
        'America/Phoenix' => 'US Mountain Standard Time',
        'America/Caracas' => 'Venezuela Standard Time',
        'Asia/Vladivostok' => 'Vladivostok Standard Time',
        'Australia/Perth' => 'W. Australia Standard Time',
        'Africa/Lagos' => 'W. Central Africa Standard Time',
        'Europe/Berlin' => 'W. Europe Standard Time',
        'Asia/Tashkent' => 'West Asia Standard Time',
        'Pacific/Port_Moresby' => 'West Pacific Standard Time',
        'Asia/Yakutsk' => 'Yakutsk Standard Time',
    ];

    /**
     * Retrieve list of timezones
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
} 