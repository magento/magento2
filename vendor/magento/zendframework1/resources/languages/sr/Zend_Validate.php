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
 * EN-Revision: 21135
 */
return array(
    // Zend_Validate_Alnum
    "Invalid type given, value should be float, string, or integer" => "Nevalidan tip, vrednost treba da bude tekst ili broj",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' sadrži karaktere koji nisu slova niti cifre",
    "'%value%' is an empty string" => "'%value%' je prazan tekst",

    // Zend_Validate_Alpha
    "Invalid type given, value should be a string" => "Nevalidan tip, vrednost treba da bude tekst",
    "'%value%' contains non alphabetic characters" => "'%value%' sadrži karaktere koji nisu slova",
    "'%value%' is an empty string" => "'%value%' je prazan tekst",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "'%value%' greška u checksum validaciji",
    "'%value%' contains invalid characters" => "'%value%' sadrži nevalidne karaktere",
    "'%value%' should have a length of %length% characters" => "'%value%' treba da bude dužine %length%",
    "Invalid type given, value should be string" => "Nevalidan tip, vrednost treba da bude tekst",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' nije između '%min%' i '%max%', uključivo",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' nije strogo između '%min%' i '%max%'",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "'%value%' nije validno",
    "Failure within the callback, exception returned" => "Greška u pozivu",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' treba da sadrži između 13 i 19 cifara",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Luhn algoritam ne prolazi na '%value%'",

    // Zend_Validate_CreditCard
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Luhn algoritam ne prolazi na '%value%'",
    "'%value%' must contain only digits" => "'%value%' treba da sadrži samo cifre",
    "Invalid type given, value should be a string" => "Nevalidan tip, vrednost treba da bude tekst",
    "'%value%' contains an invalid amount of digits" => "'%value%' sadrži nevalidu količinu cifara",
    "'%value%' is not from an allowed institute" => "'%value%' nije iz dozvoljene institucije",
    "Validation of '%value%' has been failed by the service" => "Validacija '%value%' nije uspela od strane servisa",
    "The service returned a failure while validating '%value%'" => "Servis je vratio grešku pri validaciji '%value%'",

    // Zend_Validate_Date
    "Invalid type given, value should be string, integer, array or Zend_Date" => "Nevalidan tip, vrednost treba da bude tekst, ceo broj, niz ili Zend_Date",
    "'%value%' does not appear to be a valid date" => "'%value%' nije validan datum",
    "'%value%' does not fit the date format '%format%'" => "'%value%' nije u formatu datuma '%format%'",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => "Zapis koji se poklapa sa %value% nije pronađen",
    "A record matching '%value%' was found" => "Zapis koji se poklapa sa %value% je pronađen",

    // Zend_Validate_Digits
    "Invalid type given, value should be string, integer or float" => "Nevalidan tip, vrednost treba da bude tekst ili broj",
    "'%value%' contains characters which are not digits; but only digits are allowed" => "'%value%' sadrži karaktere koji nisu cifre, a samo cifre su dozvoljene",
    "'%value%' contains not only digit characters" => "'%value%' ne sadrži samo cifre",
    "'%value%' is an empty string" => "'%value%' je prazan tekst",

    // Zend_Validate_EmailAddress
    "Invalid type given, value should be a string" => "Nevalidan tip, vrednost treba da bude tekst",
    "'%value%' is not a valid email address in the basic format local-part@hostname" => "'%value%' nije validna adresa elektronske pošte u formatu adresa@imehosta",
    "'%hostname%' is not a valid hostname for email address '%value%'" => "'%hostname%' nije validno ime hosta za adresu elektronske pošte '%value%'",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "'%hostname%' nema validan MX zapis za adresu elektronske pošte '%value%'",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network." => "'%hostname%' nije rutabilan mrežni segment. Adresa elektronske pošte '%value%' ne treba da bude razrešena sa javne mreže",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart%' se ne poklapa sa dot-atom formatom",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' se ne poklapa sa quoted-string formatom",
    "'%localPart%' is not a valid local part for email address '%value%'" => "'%localPart%' nije validan deo adrese elektronske pošte '%value%'",
    "'%value%' exceeds the allowed length" => "'%value%' prelazi dozvoljenu dužinu",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Preveliki broj fajlova, maksimalno '%max%' je dozvoljeno, a '%count%' je prosleđeno",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Premali broj fajlova, minimalno '%min%' je očekivano, a '%count%' je prosleđeno",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "Fajl '%value%' ne prolazi crc32 proveru",
    "A crc32 hash could not be evaluated for the given file" => "Nema crc32 kodova za dati fajl",
    "File '%value%' could not be found" => "Fajl '%value%' ne može biti pronađen",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "Fajl '%value%' ima nevalidnu ekstenziju",
    "File '%value%' could not be found" => "Fajl '%value%' ne može biti pronađen",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "Fajl '%value%' ima nevalidan mime-tip '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Mime-tip fajla '%value%' ne može biti detektovan",
    "File '%value%' can not be read" => "Fajl '%value%' ne može biti pročitan",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "Fajl '%value%' ne postoji",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "Fajl '%value%' ima nevalidnu ekstenziju",
    "File '%value%' could not be found" => "Fajl '%value%' ne može biti pronađen",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Svi fajlovi u zbiru treba da imaju maksimalnu veličinu '%max%', veličina poslatih fajlova je '%size%'",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Svi fajlovi u zbiru treba da imaju minimalnu veličinu '%min%', veličina poslatih fajlova je '%size%'",
    "One or more files can not be read" => "Jedan ili više fajlova ne može biti pročitan",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "Fajl '%value%' je nepravilno kodiran",
    "A hash could not be evaluated for the given file" => "Heševi nisu pronađeni za dati fajl",
    "File '%value%' could not be found" => "Fajl '%value%' ne može biti pronađen",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "Maksimalna dozvoljena širina slike '%value%' je '%maxwidth%', data slika ima širinu '%width%'",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "Minimalna očekivana širina slike '%value%' je '%minwidth%', data slika ima širinu '%width%'",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "Maksimalna dozvoljena visina slike '%value%' je '%maxheight%', data slika ima visinu '%height%'",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "Minimalna očekivana visina slike '%value%' je '%minheight%', data slika ima visinu '%height%'",
    "The size of image '%value%' could not be detected" => "Veličina slike '%value%' ne može biti određena",
    "File '%value%' can not be read" => "Fajl '%value%' ne može biti pročitan",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => "Fajl '%value%' nije kompresovan, '%type%' detektovan",
    "The mimetype of file '%value%' could not be detected" => "Mime-tip fajla '%value%' ne može biti detektovan",
    "File '%value%' can not be read" => "Fajl '%value%' ne može biti pročitan",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "Fajl '%value%' nije slika, '%type%' detektovan",
    "The mimetype of file '%value%' could not be detected" => "Mime-tip fajla '%value%' ne može biti detektovan",
    "File '%value%' can not be read" => "Fajl '%value%' ne može biti pročitan",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "Fajl '%value%' ne prolazi md5 proveru",
    "A md5 hash could not be evaluated for the given file" => "Nema md5 heševa za dati fajl",
    "File '%value%' could not be found" => "Fajl '%value%' ne može biti pronađen",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "Fajl '%value%' ima nevalidan mime-tip '%type%'",
    "The mimetype of file '%value%' could not be detected" => "Mime-tip fajla '%value%' ne može biti detektovan",
    "File '%value%' can not be read" => "Fajl '%value%' ne može biti pročitan",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "Fajl '%value%' postoji",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "Fajl '%value%' ne prolazi sha1 proveru",
    "A sha1 hash could not be evaluated for the given file" => "Nema sha1 heševa za dati fajl",
    "File '%value%' could not be found" => "Fajl '%value%' ne može biti pronađen",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "Maksimalna dozvoljena veličina fajla '%value%' je '%max%', data veličina je '%size%'",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "Minimalna očekivana veličina fajla '%value%' je '%min%', data veličina je '%size%'",
    "File '%value%' could not be found" => "Fajl '%value%' ne može biti pronađen",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "Fajl '%value%' prevazilazi maksimalnu dozvoljenu veličinu",
    "File '%value%' exceeds the defined form size" => "Fajl '%value%' prevazilazi maksimalnu dozvoljenu veličinu",
    "File '%value%' was only partially uploaded" => "Fajl '%value%' je samo parcijalno uploadovan",
    "File '%value%' was not uploaded" => "Fajl '%value%' nije uploadovan",
    "No temporary directory was found for file '%value%'" => "Privremeni direktorijum nije pronađen za fajl '%value%'",
    "File '%value%' can't be written" => "Fajl '%value%' ne može biti izmenjen",
    "A PHP extension returned an error while uploading the file '%value%'" => "Ekstenzija je vratila grešku tokom uploada fajla '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Fajl '%value%' je ilegalno uploadovan, moguć napad",
    "File '%value%' was not found" => "Fajl '%value%' nije pronađen",
    "Unknown error while uploading file '%value%'" => "Nepoznata greška pri uploadu fajla '%value%'",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "Previše reči, maksimalno '%max%' je dozvoljeno, '%count%' je izbrojano",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "Premalo reči, minimalno '%min%' je očekivano, '%count%' je izbrojano",
    "File '%value%' could not be found" => "Fajl '%value%' ne može biti pronađen",

    // Zend_Validate_Float
    "Invalid type given, value should be float, string, or integer" => "Nevalidan tip, vrednost treba da bude tekst ili broj",
    "'%value%' does not appear to be a float" => "'%value%' nije razlomljeni broj",

    // Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => "'%value%' nije veće od '%min%'",

    // Zend_Validate_Hex
    "Invalid type given, value should be a string" => "Nevalidan tip, vrednost treba da bude tekst",
    "'%value%' has not only hexadecimal digit characters" => "'%value%' se ne sastoji samo od heksadecimalnih karaktera",

    // Zend_Validate_Hostname
    "Invalid type given, value should be a string" => "Nevalidan tip, vrednost treba da bude tekst",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => "'%value%' je IP adresa, IP adrese nisu dozvoljene",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => "'%value%' je DNS ime hosta, ali TLD nije u listi poznatih",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => "'%value%' je DNS ime hosta, ali sadrži srednju crtu (-) na nedozvoljenoj poziciji",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "'%value%' je DNS ime hosta, ali se ne poklapa sa šemom za '%tld%' TLD",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => "'%value%' je DNS ime hosta, ali ne može da se ekstraktuje TLD deo '%tld%'",
    "'%value%' does not match the expected structure for a DNS hostname" => "'%value%' se ne poklapa sa očekivanom strukturom DNS imena hosta",
    "'%value%' does not appear to be a valid local network name" => "'%value%' nije validno ime lokalne mreže",
    "'%value%' appears to be a local network name but local network names are not allowed" => "'%value%' je ime lokalne mreže, lokalna imena mreža nisu dozvoljena",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => "'%value%' je DNS ime hosta, ali data punikod notacija ne može biti dekodirana",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "Nepoznata zemlja u IBAN '%value%'",
    "'%value%' has a false IBAN format" => "'%value%' nije u validnom IBAN formatu",
    "'%value%' has failed the IBAN check" => "'%value%' ne prolazi IBAN proveru",

    // Zend_Validate_Identical
    "The two given tokens do not match" => "Tokeni se ne poklapaju",
    "No token was provided to match against" => "Token za proveru nije prosleđen",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => "'%value%' nije pronađeno u gomili",

    // Zend_Validate_Int
    "Invalid type given, value should be string or integer" => "Nevalidan tip, vrednost treba da bude tekst ili ceo broj",
    "'%value%' does not appear to be an integer" => "'%value%' nije ceo broj",

    // Zend_Validate_Ip
    "Invalid type given, value should be a string" => "Nevalidan tip, vrednost treba da bude tekst",
    "'%value%' does not appear to be a valid IP address" => "'%value%' nije validna IP adresa",

    // Zend_Validate_Isbn
    "Invalid type given, value should be string or integer" => "Nevalidan tip, vrednost treba da bude tekst ili ceo broj",
    "'%value%' is not a valid ISBN number" => "'%value%' nije validan ISBN broj",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => "'%value%' je manje od '%max%'",

    // Zend_Validate_NotEmpty
    "Invalid type given, value should be float, string, array, boolean or integer" => "Nevalidan tip, vrednost treba da bude tekst, broj ili logička vrednost",
    "Value is required and can't be empty" => "Vrednost je obavezna i ne sme biti prazna",

    // Zend_Validate_PostCode
    "Invalid type given. The value should be a string or a integer" => "Nevalidan tip. Vrednost treba da bude tekst ili ceo broj",
    "'%value%' does not appear to be a postal code" => "'%value%' nije poštanski broj",

    // Zend_Validate_Regex
    "Invalid type given, value should be string, integer or float" => "Nevalidan tip, vrednost treba da bude tekst ili broj",
    "'%value%' does not match against pattern '%pattern%'" => "'%value%' se ne poklapa sa formatom '%pattern%'",
    "There was an internal error while using the pattern '%pattern%'" => "Dogodila se greška pri korišćenju formata '%pattern%'",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is not a valid sitemap changefreq" => "'%value%' nije validna frekvencija promene mape sajta",
    "Invalid type given, the value should be a string" => "Nevalidan tip, vrednost treba da bude tekst",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is not a valid sitemap lastmod" => "'%value%' nije validan datum izmene mape sajta",
    "Invalid type given, the value should be a string" => "Nevalidan tip, vrednost treba da bude tekst",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is not a valid sitemap location" => "'%value%' nije validna lokacija mape sajta",
    "Invalid type given, the value should be a string" => "Nevalidan tip, vrednost treba da bude tekst",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is not a valid sitemap priority" => "'%value%' nije validan prioritet mape sajta",
    "Invalid type given, the value should be a integer, a float or a numeric string" => "Nevalidan tip, vrednost treba da bude broj ili numerički niz",

    // Zend_Validate_StringLength
    "Invalid type given, value should be a string" => "Nevalidan tip, vrednost treba da bude tekst",
    "'%value%' is less than %min% characters long" => "'%value%' ima manje od %min% karaktera",
    "'%value%' is more than %max% characters long" => "'%value%' ima više od %max% karaktera",
);
