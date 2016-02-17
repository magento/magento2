<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Translate
 * @subpackage Ressource
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id:$
 */

/**
 * EN-Revision: 21134
 */
return array(
    // Zend_Validate_Alnum
    "Invalid type given, value should be float, string, or integer" => "Неприпустимий тип даних, значення повинно бути числом з плаваючою крапкою, рядком чи цілим числом",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' містить символи які не є літерами чи цифрами",
    "'%value%' is an empty string" => "'%value%' - пустий рядок",

    // Zend_Validate_Alpha
    "Invalid type given, value should be a string" => "Неприпустимий тип даних, значення повинно бути рядком",
    "'%value%' contains non alphabetic characters" => "'%value%' містить символи які не є літерами",
    "'%value%' is an empty string" => "'%value%' - пустий рядок",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "'%value%' помилка перевірки контрольної суми",
    "'%value%' contains invalid characters" => "'%value%' містить неприпустимі символи",
    "'%value%' should have a length of %length% characters" => "Довжина '%value%' повинна складати %length% символів",
    "Invalid type given, value should be string" => "Неприпустимий тип даних, значення повинно бути рядком",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' за межами діапазону від '%min%' до '%max%', включно",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' за межами діапазону від '%min%' до '%max%'",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "'%value%' - неприпустиме значення",
    "Failure within the callback, exception returned" => "Помилка в зворотньому виклику, повернено виключення",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' має містити від 13 до 19 цифр",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Алгоритм Луна (обчислення контрольної цифри) повернув помилку для '%value%'",

    // Zend_Validate_CreditCard
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Алгоритм Луна (обчислення контрольної цифри) повернув помилку для '%value%'",
    "'%value%' must contain only digits" => "'%value%' має містити тільки цифри",
    "Invalid type given, value should be a string" => "Неприпустимий тип даних, значення повинно бути рядком",
    "'%value%' contains an invalid amount of digits" => "'%value%' містить неприпустиму кількість цифр",
    "'%value%' is not from an allowed institute" => "'%value%' не відноситься до дозволенних платіжних систем",
    "Validation of '%value%' has been failed by the service" => "Перевірка '%value%' закінчилась помилкою сервісу",
    "The service returned a failure while validating '%value%'" => "Сервіс повернув помилку під час перевірки '%value%'",

    // Zend_Validate_Date
    "Invalid type given, value should be string, integer, array or Zend_Date" => "Неприпустимий тип даних, значення повинно бути рядком, цілим числом, масивом чи об'єктом Zend_Date",
    "'%value%' does not appear to be a valid date" => "'%value%' - некоректна дата",
    "'%value%' does not fit the date format '%format%'" => "'%value%' не відповідає формату дати '%format%'",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => "Не знайдено записів, що відповідають '%value%'",
    "A record matching '%value%' was found" => "Знайдено запис, що відповідає '%value%'",

    // Zend_Validate_Digits
    "Invalid type given, value should be string, integer or float" => "Неприпустимий тип даних, значення повинно бути числом з плаваючою крапкою, рядком чи цілим числом",
    "'%value%' contains characters which are not digits; but only digits are allowed" => "'%value%' має містити тільки цифри",
    "'%value%' is an empty string" => "'%value%' - пустий рядок",

    // Zend_Validate_EmailAddress
    "Invalid type given, value should be a string" => "Неприпустимий тип даних, значення повинно рядком",
    "'%value%' is not a valid email address in the basic format local-part@hostname" => "'%value%' неприпустима адреса електронної пошти для формату ім'я@домен",
    "'%hostname%' is not a valid hostname for email address '%value%'" => "'%hostname%' неприпустиме ім'я хоста для адреси '%value%'",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "'%hostname%' не має коректного MX-запису про адресу '%value%'",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network." => "'%hostname%' не є маршрутизованим сегментом мережі. Адреса електронної пошти '%value%' не може бути отримана з публічної мережі.",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart% не відповідає формату dot-atom",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' не відповідає формату quoted-string",
    "'%localPart%' is not a valid local part for email address '%value%'" => "'%localPart%' неприпустиме ім'я для адреси '%value%'",
    "'%value%' exceeds the allowed length" => "'%value%' перевищує дозволену довжину",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Занадто багато файлів, дозволено максимум - '%max%', отримано - '%count%'",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Занадто мало файлів, дозволено мінімум - '%min%', отримано - '%count%'",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "Файл '%value%' не відповідає заданому crc32 хешу",
    "A crc32 hash could not be evaluated for the given file" => "crc32 хеш не може бути обчисленний для цього файлу",
    "File '%value%' could not be found" => "Файл '%value%' не знайдено",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "Файл '%value%' має неприпустиме розширення",
    "File '%value%' could not be found" => "Файл '%value%' не знайдено",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "Файл '%value%' має неприпустимий MIME-тип '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Не вдається визначити MIME-тип файлу '%value%'",
    "File '%value%' can not be read" => "Файл '%value%' неможливо прочитати",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "Файл '%value%' не існує",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "Файл '%value%' має неприпустиме розширення",
    "File '%value%' could not be found" => "Файл '%value%' не знайдено",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Загальний розмір файлів не повинен перевищувати '%max%', зараз - '%size%'",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Загальний розмір файлів має бути менше '%min%', зараз - '%size%'",
    "One or more files can not be read" => "Неможливо прочитати один чи декілька файлів",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "Файл '%value%' не відповідає вказаному хешу",
    "A hash could not be evaluated for the given file" => "Не можливо обчислити хеш для вказаного файла",
    "File '%value%' could not be found" => "Файл '%value%' не знайдено",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "Максимально допустима ширина для зображення '%value%' - '%maxwidth%', зараз - '%width%'",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "Мінімально очікувана ширина для зображення '%value%' - '%minwidth%', зараз - '%width%'",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "Максимально допустима висота для зображення '%value%' - '%maxheight%', зараз - '%height%'",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "Мінімально очікувана ширина для зображення '%value%' - '%minheight%', зараз - '%height%'",
    "The size of image '%value%' could not be detected" => "Неможливо визначити розмір зображення '%value%'",
    "File '%value%' can not be read" => "Файл '%value%' неможливо прочитати",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => "Файл '%value%' не є стислий. MIME-тип файлу - '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Неможливо визначити MIME-тип файлу '%value%'",
    "File '%value%' can not be read" => "Файл '%value%' неможливо прочитати",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "Файл '%value%' не є зображенням. MIME-тип файлу - '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Неможливо визначити MIME-тип файлу '%value%'",
    "File '%value%' can not be read" => "Файл '%value%' неможливо прочитати",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "Файл '%value%' не відповідає вказаному md5 хешу",
    "A md5 hash could not be evaluated for the given file" => "md5 хеш не може бути визначений для вказаного файлу",
    "File '%value%' could not be found" => "Файл '%value%' не знайдено",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "MIME-тип '%type%' файлу '%value%' неприпустимий",
    "The mimetype of file '%value%' could not be detected" => "Неможливо визначити MIME-тип файлу '%value%'",
    "File '%value%' can not be read" => "Файл '%value%' не можливо прочитати",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "Файл '%value%' вже існує",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "Файл '%value%' не відповідає хешу sha1",
    "A sha1 hash could not be evaluated for the given file" => "Неможливо визначити sha1 хеш для вказаного файлу",
    "File '%value%' could not be found" => "Файл '%value%' не знайдено",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "Максимально дозволений розмір файлу '%value%' - '%max%', зараз - '%size%'",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "Мінімально дозволений розмір файлу '%value%' - '%min%', зараз - '%size%'",
    "File '%value%' could not be found" => "Файл '%value%' не знайдено",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "Розмір файлу '%value%' більше ніж дозволений, що вказаний в php.ini",
    "File '%value%' exceeds the defined form size" => "Розмір файлу '%value%' більше ніж дозволений, що вказаний  в формі",
    "File '%value%' was only partially uploaded" => "Файл '%value%' був завантажений тільки частково",
    "File '%value%' was not uploaded" => "Файл '%value%' не був завантажений",
    "No temporary directory was found for file '%value%'" => "Не знайдено тимчасову директорію для файлу '%value%'",
    "File '%value%' can't be written" => "Файл '%value%' не може бути записаний",
    "A PHP extension returned an error while uploading the file '%value%'" => "PHP розширення повернуло помилку під час завантаження фалу '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Файл '%value%' завантажений некоректно. Можлива атака",
    "File '%value%' was not found" => "Файл '%value%' не знайдено",
    "Unknown error while uploading file '%value%'" => "Під час завантаження файлу '%value%' виникла невідома помилка",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "Занадто багато слів, дозволено максимум '%max%' слів, зараз - '%count%'",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "Занадто мало слів, дозволено мінімум '%min%' слів, зараз - '%count%'",
    "File '%value%' could not be found" => "Файл '%value%' не знайдено",

    // Zend_Validate_Float
    "Invalid type given, value should be float, string, or integer" => "Неприпустимий тип даних, значення повинно бути числом з плаваючою крапкою, рядком, або цілим числом",
    "'%value%' does not appear to be a float" => "'%value%' не є числом з плаваючою крапкою",

    // Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => "'%value%' не більше ніж '%min%'",

    // Zend_Validate_Hex
    "Invalid type given, value should be a string" => "Неприпустимий тип даних, значення повинно бути рядком",
    "'%value%' has not only hexadecimal digit characters" => "Значення '%value%' повинно містити тільки шістнадцятирічні символи",

    // Zend_Validate_Hostname
    "Invalid type given, value should be a string" => "Неприпустимий тип даних, значення повинно бути рядком",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => "Значення '%value%' виглядає як IP-адреса, але IP-адреси не дозволені",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => "'%value%' виглядає як DNS ім’я хоста, але воно не повинно бути зі списку доменів верхнього рівня",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => "'%value%' виглядає як DNS ім’я хоста, але знак '-' знаходиться в неприпустимому місці",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "'%value%' виглядає як DNS ім’я хоста, але воно не відповідає шаблону для доменних імен верхнього рівня '%tld%'",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => "'%value%' виглядає як DNS ім’я хоста, але не вдається визначити домен верхнього рівня",
    "'%value%' does not match the expected structure for a DNS hostname" => "'%value%' не відповідає очікуваній структурі для DNS імені хоста",
    "'%value%' does not appear to be a valid local network name" => "'%value%' є неприпустимим іменем локальної мережі",
    "'%value%' appears to be a local network name but local network names are not allowed" => "'%value%' виглядає як ім’я локальної мережі, але імена локальних мереж не дозволені",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => "'%value%' виглядає як DNS ім’я хоста, але вказане значення не може бути перетворене в припустимий для DNS набір символів",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "Невідома країна IBAN '%value%'",
    "'%value%' has a false IBAN format" => "'%value%' має неприпустимий IBAN формат",
    "'%value%' has failed the IBAN check" => "'%value%' не пройшло IBAN перевірку",

    // Zend_Validate_Identical
    "The token '%token%' does not match the given token '%value%'" => "Значення '%token%' не співпадає з вказаним значенням '%value%'",
    "No token was provided to match against" => "Не вказано значення для перевірки на ідентичність",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => "'%value%' не знайдено в списку допустимих значень",

    // Zend_Validate_Int
    "Invalid type given, value should be string or integer" => "Неприпустимий тип даних, значення повинно бути рядком чи цілим числом",
    "'%value%' does not appear to be an integer" => "'%value%' не є цілим числом",

    // Zend_Validate_Ip
    "Invalid type given, value should be a string" => "Неприпустимий тип даних, значення повинно бути рядком",
    "'%value%' does not appear to be a valid IP address" => "'%value%' - некоректна IP-адреса",

    // Zend_Validate_Isbn
    "'%value%' is not a valid ISBN number" => "'%value%' - некоректний номер ISBN",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => "'%value%' не менше ніж '%max%'",

    // Zend_Validate_NotEmpty
    "Invalid type given, value should be float, string, array, boolean or integer" => "Неприпустимий тип даних, значення повинно бути числом з плаваючою крапкою, рядком, масивом чи цілим числом",
    "Value is required and can't be empty" => "Значення обов'язкове і не може бути порожнім",

    // Zend_Validate_PostCode
    "Invalid type given, value should be string or integer" => "Неприпустимий тип даних, значення повинно бути рядком чи цілим числом",
    "'%value%' does not appear to be an postal code" => "'%value%' не являється поштовим індексом",

    // Zend_Validate_Regex
    "Invalid type given, value should be string, integer or float" => "Неприпустимий тип даних, значення повинно бути числом з плаваючою крапкою, рядком чи цілим числом",
    "'%value%' does not match against pattern '%pattern%'" => "'%value%' не відповідає шаблону '%pattern%'",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is not a valid sitemap changefreq" => "'%value%' неприпустиме значення для sitemap changefreq",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is not a valid sitemap lastmod" => "'%value%' неприпустиме значення для sitemap lastmod",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is not a valid sitemap location" => "'%value%' неприпустиме значення для sitemap location",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is not a valid sitemap priority" => "'%value%' неприпустиме значення для sitemap priority",

    // Zend_Validate_StringLength
    "Invalid type given, value should be a string" => "Неприпустимий тип даних, значення повинно бути рядком",
    "'%value%' is less than %min% characters long" => "Довжина '%value%' менше %min% символів",
    "'%value%' is more than %max% characters long" => "Довжина '%value%' перевищує %max% символів",
);
