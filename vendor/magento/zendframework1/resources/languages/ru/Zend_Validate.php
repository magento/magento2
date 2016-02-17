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
 * EN-Revision: 20377
 */
return array(
    // Zend_Validate_Alnum
    "Invalid type given, value should be float, string, or integer" => "Недопустимый тип данных, значение должно быть числом с плавающей точкой, строкой или целым числом",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' содержит недопустимые символы. Разрешены только буквенные символы и цифры",
    "'%value%' is an empty string" => "'%value%' - пустая строка",

    // Zend_Validate_Alpha
    "Invalid type given, value should be a string" => "Недопустимый тип данных, значение должно быть строкой",
    "'%value%' contains non alphabetic characters" => "'%value%' содержит не буквенные символы",
    "'%value%' is an empty string" => "'%value%' - пустая строка",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "'%value%' ошибка проверки контрольной суммы",
    "'%value%' contains invalid characters" => "'%value%' содержит недопустимые символы",
    "'%value%' should have a length of %length% characters" => "Длина '%value%' должна составлять %length% символов",
    "Invalid type given, value should be string" => "Недопустимый тип данных, значение должно быть строкой",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' не в диапазоне от '%min%' до '%max%', включительно",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' не в диапазоне от '%min%' до '%max%'",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "'%value%' недопустимое значение",
    "Failure within the callback, exception returned" => "Ошибка в обратном вызове, возвращено исключение",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' должно содержать от 13 до 19 цифр",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Алгоритм Луна (вычисление контрольной цифры) вернул ошибку для '%value%'",

    // Zend_Validate_CreditCard
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Алгоритм Луна (вычисление контрольной цифры) вернул ошибку для '%value%'",
    "'%value%' must contain only digits" => "'%value%' должно содержать только цифры",
    "Invalid type given, value should be a string" => "Недопустимый тип данных, значение должно быть строкой",
    "'%value%' contains an invalid amount of digits" => "'%value%' содержит недопустимое количество цифр",
    "'%value%' is not from an allowed institute" => "'%value%' не входит в список разрешенных платежных систем",
    "Validation of '%value%' has been failed by the service" => "Проверка '%value%' закончилась ошибкой сервиса",
    "The service returned a failure while validating '%value%'" => "Сервис возвратил ошибку во время проверки '%value%'",

    // Zend_Validate_Date
    "Invalid type given, value should be string, integer, array or Zend_Date" => "Недопустимый тип данных, значение должно быть строкой, целым числом, массивом или объектом Zend_Date",
    "'%value%' does not appear to be a valid date" => "'%value%' не является корректной датой",
    "'%value%' does not fit the date format '%format%'" => "'%value%' не соответствует формату даты '%format%'",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => "Не найдено записей, совпадающих с '%value%'",
    "A record matching '%value%' was found" => "Найдена запись, совпадающая со значением '%value%'",

    // Zend_Validate_Digits
    "Invalid type given, value should be string, integer or float" => "Недопустимый тип данных, значение должно быть числом с плавающей точкой, строкой, или целым числом",
    "'%value%' contains not only digit characters" => "Значение '%value%' должно содержать только цифровые символы",
    "'%value%' is an empty string" => "'%value%' - пустая строка",

    // Zend_Validate_EmailAddress
    "Invalid type given, value should be a string" => "Недопустимый тип данных, значение должно быть строкой",
    "'%value%' is not a valid email address in the basic format local-part@hostname" => "'%value%' недопустимый адрес электронной почты. Введите его в формате имя@домен",
    "'%hostname%' is not a valid hostname for email address '%value%'" => "'%hostname%' недопустимое имя хоста для адреса '%value%'",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "'%hostname%' не имеет корректной MX-записи об адресе '%value%'",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network." => "'%hostname%' не является маршрутизируемым сегментом сети. Адрес электронной почты '%value%' не может быть получен из публичной сети.",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart% не соответствует формату dot-atom",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' не соответствует формату quoted-string",
    "'%localPart%' is not a valid local part for email address '%value%'" => "'%localPart%' недопустимое имя для адреса '%value%'",
    "'%value%' exceeds the allowed length" => "'%value%' превышает допустимую длину",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Слишком много файлов, максимально разрешено - '%max%', а получено - '%count%'",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Слишком мало файлов, минимально разрешено - '%min%', а получено - '%count%'",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "Файл '%value%' не соответствует заданному crc32 хешу",
    "A crc32 hash could not be evaluated for the given file" => "crc32 хеш не может быть вычислен для данного файла",
    "File '%value%' could not be found" => "Файл '%value%' не найден",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "Файл '%value%' имеет недопустимое расширение",
    "File '%value%' could not be found" => "Файл '%value%' не найден",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "MIME-тип '%type%' файла '%value%' недопустим",
    "The mimetype of file '%value%' could not be detected" => "Не удается определить MIME-тип файла '%value%'",
    "File '%value%' can not be read" => "Файл '%value%' не может быть прочитан",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "Файл '%value%' не существует",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "Файл '%value%' имеет недопустимое расширение",
    "File '%value%' could not be found" => "Файл '%value%' не найден",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Общий размер файлов не должен превышать '%max%', сейчас - '%size%'",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Общий размер файлов не должен быть менее '%min%', сейчас - '%size%'",
    "One or more files can not be read" => "Один или более файлов не могут быть прочитаны",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "Файл '%value%' не соответствует указанному хешу",
    "A hash could not be evaluated for the given file" => "Хеш не может быть подсчитан для указанного файла",
    "File '%value%' could not be found" => "Файл '%value%' не найден",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "Максимально разрешённая ширина изображения '%value%' должна быть '%maxwidth%', сейчас - '%width%'",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "Минимально ожидаемая ширина изображения '%value%' должна быть '%minwidth%', сейчас - '%width%'",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "Максимально разрешённая высота изображения '%value%' должна быть '%maxheight%', сейчас - '%height%'",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "Минимально ожидаемая высота изображения '%value%' должна быть '%minheight%', сейчас - '%height%'",
    "The size of image '%value%' could not be detected" => "Невозможно определить размер изображения '%value%'",
    "File '%value%' can not be read" => "Файл '%value%' не может быть прочитан",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => "Файл '%value%' не является сжатым. MIME-тип файла - '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Не удается определить MIME-тип файла '%value%'",
    "File '%value%' can not be read" => "Файл '%value%' не может быть прочитан",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "Файл '%value%' не является изображением. MIME-тип файла - '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Не удается определить MIME-тип файла '%value%'",
    "File '%value%' can not be read" => "Файл '%value%' не может быть прочитан",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "Файл '%value%' не соответствует указанному md5 хешу",
    "A md5 hash could not be evaluated for the given file" => "md5 хеш не может быть вычислен для указанного файла",
    "File '%value%' could not be found" => "Файл '%value%' не найден",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "MIME-тип '%type%' файла '%value%' недопустим",
    "The mimetype of file '%value%' could not be detected" => "Не удается определить MIME-тип файла '%value%'",
    "File '%value%' can not be read" => "Файл '%value%' не может быть прочитан",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "Файл '%value%' уже существует",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "Файл '%value%' не соответствует указаному хешу sha1",
    "A sha1 hash could not be evaluated for the given file" => "Хеш sha1 не может быть подсчитан для указанного файла",
    "File '%value%' could not be found" => "Файл '%value%' не найден",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "Максимальный разрешенный размер файла '%value%' это '%max%', сейчас - '%size%'",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "Минимальный разрешенный размер файла '%value%' это '%min%', сейчас - '%size%'",
    "File '%value%' could not be found" => "Файл '%value%' не найден",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "Размер файла '%value%' превышает допустимый размер, указанный в php.ini",
    "File '%value%' exceeds the defined form size" => "Размер файла '%value%' превышает допустимый размер, указанный в форме",
    "File '%value%' was only partially uploaded" => "Файл '%value%' был загружен только частично",
    "File '%value%' was not uploaded" => "Файл '%value%' не был загружен",
    "No temporary directory was found for file '%value%'" => "Не найдена временная директория для файла '%value%'",
    "File '%value%' can't be written" => "Файл '%value%' не может быть записан",
    "A PHP extension returned an error while uploading the file '%value%'" => "PHP расширение возвратило ошибку во время загрузки файла '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Файл '%value%' загружен некорректно. Возможна атака",
    "File '%value%' was not found" => "Файл '%value%' не найден",
    "Unknown error while uploading file '%value%'" => "Произошла неизвестная ошибка во время загрузки файла '%value%'",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "Слишком много слов, разрешено максимум '%max%' слов, но сейчас - '%count%'",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "Слишком мало слов, разрешено минимум '%min%' слов, но сейчас - '%count%'",
    "File '%value%' could not be found" => "Файл '%value%' не найден",

    // Zend_Validate_Float
    "Invalid type given, value should be float, string, or integer" => "Недопустимый тип данных, значение должно быть числом с плавающей точкой, строкой, или целым числом",
    "'%value%' does not appear to be a float" => "'%value%' не является числом с плавающей точкой",

    // Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => "'%value%' не превышает '%min%'",

    // Zend_Validate_Hex
    "Invalid type given, value should be a string" => "Недопустимый тип данных, значение должно быть строкой",
    "'%value%' has not only hexadecimal digit characters" => "Значение '%value%' должно содержать только шестнадцатиричные символы",

    // Zend_Validate_Hostname
    "Invalid type given, value should be a string" => "Недопустимый тип данных, значение должно быть строкой",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => "Значение '%value%' выглядит как IP-адрес, но IP-адреса не разрешены",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => "'%value%' выглядит как DNS имя хоста, но оно не дожно быть из списка доменов верхнего уровня",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => "'%value%' выглядит как DNS имя хоста, но знак '-' находится в недопустимом месте",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "'%value%' выглядит как DNS имя хоста, но оно не соответствует шаблону для доменных имен верхнего уровня '%tld%'",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => "'%value%' выглядит как DNS имя хоста, но не удаётся извлечь домен верхнего уровня",
    "'%value%' does not match the expected structure for a DNS hostname" => "'%value%' не соответствует ожидаемой структуре для DNS имени хоста",
    "'%value%' does not appear to be a valid local network name" => "'%value%' является недопустимым локальным сетевым адресом",
    "'%value%' appears to be a local network name but local network names are not allowed" => "'%value%' выглядит как локальный сетевой адрес, но локальные сетевые адреса не разрешены",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => "'%value%' выглядит как DNS имя хоста, но указанное значение не может быть преобразованно в допустимый для DNS набор символов",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "Не известная страна IBAN '%value%'",
    "'%value%' has a false IBAN format" => "'%value%' имеет недопустимый IBAN формат",
    "'%value%' has failed the IBAN check" => "'%value%' не прошло IBAN проверку",

    // Zend_Validate_Identical
    "The token '%token%' does not match the given token '%value%'" => "Значение '%token%' не совпадает с указанным значением '%value%'",
    "No token was provided to match against" => "Не было указано значение для проверки на идентичность",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => "'%value%' не найдено в перечисленных допустимых значениях",

    // Zend_Validate_Int
    "Invalid type given, value should be string or integer" => "Недопустимый тип данных, значение должно быть строкой или целым числом",
    "'%value%' does not appear to be an integer" => "'%value%' не является целым числом",

    // Zend_Validate_Ip
    "Invalid type given, value should be a string" => "Недопустимый тип данных, значение должно быть строкой",
    "'%value%' does not appear to be a valid IP address" => "'%value%' не является корректным IP-адресом",

    // Zend_Validate_Isbn
    "'%value%' is not a valid ISBN number" => "'%value%' не является корректным номером ISBN",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => "'%value%' не меньше, чем '%max%'",

    // Zend_Validate_NotEmpty
    "Invalid type given, value should be float, string, array, boolean or integer" => "Недопустимый тип данных, значение должно быть числом с плавающей точкой, строкой, массивом, булевым значением или целым числом",
    "Value is required and can't be empty" => "Значение обязательно для заполнения и не может быть пустым",

    // Zend_Validate_PostCode
    "Invalid type given, value should be string or integer" => "Недопустимый тип данных, значение должно быть строкой или целым числом",
    "'%value%' does not appear to be an postal code" => "'%value%' не является корректным почтовым кодом",

    // Zend_Validate_Regex
    "Invalid type given, value should be string, integer or float" => "Недопустимый тип данных, значение должно быть числом с плавающей точкой, строкой, или целым числом",
    "'%value%' does not match against pattern '%pattern%'" => "'%value%' не соответствует шаблону '%pattern%'",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is not a valid sitemap changefreq" => "'%value%' недопустимое значение для sitemap changefreq",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is not a valid sitemap lastmod" => "'%value%' недопустимое значение для sitemap lastmod",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is not a valid sitemap location" => "'%value%' недопустимое значение для sitemap location",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is not a valid sitemap priority" => "'%value%' недопустимое значение для sitemap priority",

    // Zend_Validate_StringLength
    "Invalid type given, value should be a string" => "Недопустимый тип данных, значение должно быть строкой",
    "'%value%' is less than %min% characters long" => "'%value%' меньше разрешенной минимальной длины в %min% символов",
    "'%value%' is more than %max% characters long" => "'%value%' больше разрешенной максимальной длины в %max% символов",
);
