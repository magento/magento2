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
 */

/**
 * HR-Revision: 2
 */
return array(
    // Zend_Validate_Alnum
    "Invalid type given, value should be float, string, or integer" => "Neispravan tip, vrijednost bi trebala biti niz slova, brojki ili realni broj",
    "'%value%' contains characters which are non alphabetic and no digits" => "'%value%' sadrži znakove koji nisu ni slova ni brojke",
    "'%value%' is an empty string" => "'%value%' je prazan niz",

    // Zend_Validate_Alpha
    "Invalid type given, value should be a string" => "Neispravan tip, vrijednost mora biti niz slova",
    "'%value%' contains non alphabetic characters" => "'%value%' sadrži znakove koji nisu slova",
    "'%value%' is an empty string" => "'%value%' je prazan niz",

    // Zend_Validate_Barcode
    "'%value%' failed checksum validation" => "'%value%' nije prošao provjeru",
    "'%value%' contains invalid characters" => "'%value%' sadrži neispravne znakove",
    "'%value%' should have a length of %length% characters" => "'%value%' bi trebao imati dužinu od %length% znakova",
    "Invalid type given, value should be string" => "Neispravan tip, vrijedno mora biti niz znakova",

    // Zend_Validate_Between
    "'%value%' is not between '%min%' and '%max%', inclusively" => "'%value%' nije između '%min%' i '%max%', uključivo",
    "'%value%' is not strictly between '%min%' and '%max%'" => "'%value%' nije strogo između '%min%' i '%max%'",

    // Zend_Validate_Callback
    "'%value%' is not valid" => "'%value%' nije ispravan",
    "Failure within the callback, exception returned" => "Pogreška sa povratnim pozivom, iznimka vraćena",

    // Zend_Validate_Ccnum
    "'%value%' must contain between 13 and 19 digits" => "'%value%' mora sadržavati između 13 i 19 znamenki",
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Luhn algoritam (mod-10 provjera) nije prošla na '%value%'",

    // Zend_Validate_CreditCard
    "Luhn algorithm (mod-10 checksum) failed on '%value%'" => "Luhn algoritam (mod-10 provjera) nije prošla na '%value%'",
    "'%value%' must contain only digits" => "'%value%' mora sadržavati samo znamenke",
    "Invalid type given, value should be a string" => "Neispravan tip, vrijednost mora biti niz znakova",
    "'%value%' contains an invalid amount of digits" => "'%value%' sadrži neispravan broj znamenki",
    "'%value%' is not from an allowed institute" => "'%value%' nije iz dozvoljene institucije",
    "Validation of '%value%' has been failed by the service" => "Servis nije odobrio provjeru '%value%'",
    "The service returned a failure while validating '%value%'" => "Servis je vratio pogrešku provjeravajući '%value%'",

    // Zend_Validate_Date
    "Invalid type given, value should be string, integer, array or Zend_Date" => "Neispravan tip, vrijednost mora biti niz znakova, broj, polje ili Zend_Date",
    "'%value%' does not appear to be a valid date" => "'%value%' ne izgleda kao ispravan datum",
    "'%value%' does not fit the date format '%format%'" => "'%value%' ne odgovara formatu datuma '%format%'",

    // Zend_Validate_Db_Abstract
    "No record matching '%value%' was found" => "Nije pronađen zapis koji se podudara sa %value%",
    "A record matching '%value%' was found" => "Zapis koji se podudara sa %value% je pronađen",

    // Zend_Validate_Digits
    "Invalid type given, value should be string, integer or float" => "Neispravan tip, vrijednost bi trebala biti niz slova, brojki ili realni broj",
    "'%value%' contains characters which are not digits; but only digits are allowed" => "'%value%' sadrži znakove koji nisu znamenke; samo znamenke su dozvoljene",
    "'%value%' is an empty string" => "'%value%' je prazan niz",

    // Zend_Validate_EmailAddress
    "Invalid type given, value should be a string" => "Neispravan tip, vrijednost bi trebala biti niz",
    "'%value%' is not a valid email address in the basic format local-part@hostname" => "'%value%' nije ispravna email adresa u osnovnom formatu lokalni-dio@ime-poslužitelja",
    "'%hostname%' is not a valid hostname for email address '%value%'" => "'%hostname%' nije ispravno ime poslužitelja za email adresu '%value%'",
    "'%hostname%' does not appear to have a valid MX record for the email address '%value%'" => "'%hostname%' nema ispravan MX zapis za email adresu '%value%'",
    "'%hostname%' is not in a routable network segment. The email address '%value%' should not be resolved from public network." => "'%hostname%' nije u rutabilnom mrežnom segmentu. Email adresa '%value%' ne bi smjela biti razlučiva iz javne mreže.",
    "'%localPart%' can not be matched against dot-atom format" => "'%localPart%' se ne podudara s dot-atom formatom",
    "'%localPart%' can not be matched against quoted-string format" => "'%localPart%' se ne podudara s 'quoted-string' formatom",
    "'%localPart%' is not a valid local part for email address '%value%'" => "'%localPart%' nije ispravan lokalni dio za email adresu '%value%'",
    "'%value%' exceeds the allowed length" => "'%value%' je duža od dozvoljene dužine",

    // Zend_Validate_File_Count
    "Too many files, maximum '%max%' are allowed but '%count%' are given" => "Previše datoteka, maksimalno '%max%' je dozvoljeno, a '%count%' je zadano",
    "Too few files, minimum '%min%' are expected but '%count%' are given" => "Premalo datoteka, minimalno '%min%' se očekuje a '%count%' je zadano",

    // Zend_Validate_File_Crc32
    "File '%value%' does not match the given crc32 hashes" => "Datoteka '%value%' se ne podudara sa zadanim crc32 hash-em",
    "A crc32 hash could not be evaluated for the given file" => "crc32 hash se ne može izračunati za zadanu datoteku",
    "File '%value%' could not be found" => "Datoteku '%value%' nije moguće pronaći",

    // Zend_Validate_File_ExcludeExtension
    "File '%value%' has a false extension" => "Datoteka '%value%' ima neispravnu ekstenziju",
    "File '%value%' could not be found" => "Datoteku '%value%' nije moguće pronaći",

    // Zend_Validate_File_ExcludeMimeType
    "File '%value%' has a false mimetype of '%type%'" => "Datoteka '%value%' ima neispravan 'mime' tip '%type%'",
    "The mimetype of file '%value%' could not be detected" => "'Mime' tip datoteke '%value%' nije moguće detektirati",
    "File '%value%' can not be read" => "Datoteku '%value%' nije moguće pročitati",

    // Zend_Validate_File_Exists
    "File '%value%' does not exist" => "Datoteka '%value%' ne postoji",

    // Zend_Validate_File_Extension
    "File '%value%' has a false extension" => "Datoteka '%value%' ima neispravnu ekstenziju",
    "File '%value%' could not be found" => "Datoteku '%value%' nije moguće pronaći",

    // Zend_Validate_File_FilesSize
    "All files in sum should have a maximum size of '%max%' but '%size%' were detected" => "Sve datoteke zajedno mogu imati maksimalnu veličinu od '%max%', a imaju '%size%'",
    "All files in sum should have a minimum size of '%min%' but '%size%' were detected" => "Sve datoteke zajedno moraju imati minimalnu veličinu od '%min%', a imaju '%size%'",
    "One or more files can not be read" => "Jednu ili više datoteka nije moguće pročitati",

    // Zend_Validate_File_Hash
    "File '%value%' does not match the given hashes" => "Datoteka '%value%' ne odgovara danom 'hashu'",
    "A hash could not be evaluated for the given file" => "'Hash' nije moguće izračunati za zadanu datoteku",
    "File '%value%' could not be found" => "Datoteku '%value%' nije moguće pronaći",

    // Zend_Validate_File_ImageSize
    "Maximum allowed width for image '%value%' should be '%maxwidth%' but '%width%' detected" => "Maksimalna dozvoljena širina slike '%value%' je '%maxwidth%', slika je široka '%width%'",
    "Minimum expected width for image '%value%' should be '%minwidth%' but '%width%' detected" => "Minimalna očekivana širina slike '%value%' je '%minwidth%' slika je široka '%width%'",
    "Maximum allowed height for image '%value%' should be '%maxheight%' but '%height%' detected" => "Maksimalna dozvoljena visina slike '%value%' je '%maxheight%', slika je visoka '%height%'",
    "Minimum expected height for image '%value%' should be '%minheight%' but '%height%' detected" => "Minimalna očekivana visina slike '%value%' je '%minheight%', slika je visoka '%height%'",
    "The size of image '%value%' could not be detected" => "Dimenzije slike '%value%' nije moguće otkriti",
    "File '%value%' can not be read" => "Datoteku '%value%' nije moguće pročitati",

    // Zend_Validate_File_IsCompressed
    "File '%value%' is not compressed, '%type%' detected" => "Datoteka '%value%' nije kompresirana, datoteka je tipa '%type%'",
    "The mimetype of file '%value%' could not be detected" => "'Mime' tip datoteke '%value%' nije moguće detektirati",
    "File '%value%' can not be read" => "Datoteku '%value%' nije moguće pročitati",

    // Zend_Validate_File_IsImage
    "File '%value%' is no image, '%type%' detected" => "Datoteka '%value%' nije slika, datoteka je tipa '%type%'",
    "The mimetype of file '%value%' could not be detected" => "'Mime' tip datoteke '%value%' nijem moguće detektirati",
    "File '%value%' can not be read" => "Datoteku '%value%' nije moguće pročitati",

    // Zend_Validate_File_Md5
    "File '%value%' does not match the given md5 hashes" => "Datoteka '%value%' ne odgovara zadanom md5 hash-u",
    "A md5 hash could not be evaluated for the given file" => "Md5 hash nije moguće izračunati za zadanu datoteku",
    "File '%value%' could not be found" => "Datoteku '%value%' nije moguće pronaći",

    // Zend_Validate_File_MimeType
    "File '%value%' has a false mimetype of '%type%'" => "Datoteka '%value%' ima neispravan 'mime' tip '%type%'",
    "The mimetype of file '%value%' could not be detected" => "'Mime' tip datoteke '%value%' nije moguće detektirati",
    "File '%value%' can not be read" => "Datoteku '%value%' nije moguće pročitati",

    // Zend_Validate_File_NotExists
    "File '%value%' exists" => "Datiteja '%value%' postoji",

    // Zend_Validate_File_Sha1
    "File '%value%' does not match the given sha1 hashes" => "Datoteka '%value%' ne odgovara zadanom sha1 hash-u",
    "A sha1 hash could not be evaluated for the given file" => "Sha1 hash se ne može izračunati za zadanu datoteku",
    "File '%value%' could not be found" => "Datoteku '%value%' nije moguće pronaći",

    // Zend_Validate_File_Size
    "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected" => "Maksimalna dozvoljena veličina datoteka '%value%' je '%max%', datoteka je velika '%size%'",
    "Minimum expected size for file '%value%' is '%min%' but '%size%' detected" => "Minimalna dozvoljena veličina datoteke '%value%' je '%min%', datoteka je velika '%size%'",
    "File '%value%' could not be found" => "Datoteku '%value%' nije moguće pronaći",

    // Zend_Validate_File_Upload
    "File '%value%' exceeds the defined ini size" => "Datoteka '%value%' prelazi veličinu definiranu u ini datoteci",
    "File '%value%' exceeds the defined form size" => "Datoteka '%value%' prelazi veličinu definiranu u formi",
    "File '%value%' was only partially uploaded" => "Datoteka '%value%' je samo djelomično poslana",
    "File '%value%' was not uploaded" => "Datoteka '%value%' nije poslana",
    "No temporary directory was found for file '%value%'" => "Nije pronađen privremeni direktorij za datoteku '%value%'",
    "File '%value%' can't be written" => "Datoteku '%value%' nije moguće zapisati",
    "A PHP extension returned an error while uploading the file '%value%'" => "PHP ekstenzija je vratila pogrešku prilikom slanja datoteke '%value%'",
    "File '%value%' was illegally uploaded. This could be a possible attack" => "Datoteka '%value%' je nelegalno poslana. Ovo bi mogao biti napad",
    "File '%value%' was not found" => "Datoteka '%value%' nije pronađena",
    "Unknown error while uploading file '%value%'" => "Nepoznata pogreška prilikom slanja datoteke '%value%'",

    // Zend_Validate_File_WordCount
    "Too much words, maximum '%max%' are allowed but '%count%' were counted" => "Previše riječi, maksimalno '%max%' riječi je dozvoljeno, a ima ih '%count%'",
    "Too less words, minimum '%min%' are expected but '%count%' were counted" => "Premalo riječi, očekuje se minimalno '%min%' riječi, a ima ih '%count%' ",
    "File '%value%' could not be found" => "Datoteku '%value%' nije moguće pronaći",

    // Zend_Validate_Float
    "Invalid type given, value should be float, string, or integer" => "Neispravan tip, vrijednost bi trebala biti niz slova, brojki ili realni broj",
    "'%value%' does not appear to be a float" => "'%value%' nije realni broj",

    // Zend_Validate_GreaterThan
    "'%value%' is not greater than '%min%'" => "'%value%' nije veće od '%min%'",

    // Zend_Validate_Hex
    "Invalid type given, value should be a string" => "Neispravan tip, vrijednost bi trebala biti niz",
    "'%value%' has not only hexadecimal digit characters" => "'%value%' nema samo heksadekadske znamenke",

    // Zend_Validate_Hostname
    "Invalid type given, value should be a string" => "Neispravan tup, vrijednost bi trebala biti niz",
    "'%value%' appears to be an IP address, but IP addresses are not allowed" => "'%value%' izgleda kao IP adresa, IP adrese nisu dozvoljene",
    "'%value%' appears to be a DNS hostname but cannot match TLD against known list" => "'%value%' izgleda kao DNS ime poslužitelja, ali ne mogu pronaći vršnu domenu u listi poznatih",
    "'%value%' appears to be a DNS hostname but contains a dash in an invalid position" => "'%value%' izgleda kao DNS ime poslužitelja, ali ima crtu ne neispravnoj poziciji",
    "'%value%' appears to be a DNS hostname but cannot match against hostname schema for TLD '%tld%'" => "'%value%' izgleda kao DNS ime poslužitelja ali se ne podudara sa shemom imena poslužitelja za vršnu domenu '%tld%'",
    "'%value%' appears to be a DNS hostname but cannot extract TLD part" => "'%value%' izgleda kao DNS ime poslužitelja, ali ne mogu izvući dio koji označava vršnu domenu",
    "'%value%' does not match the expected structure for a DNS hostname" => "'%value%' se ne podudara sa očekivanom strukturom DNS imena poslužitelja",
    "'%value%' does not appear to be a valid local network name" => "'%value%' ne izgleda kao ispravno ime lokalne mreže",
    "'%value%' appears to be a local network name but local network names are not allowed" => "'%value%' izgleda kao ime lokalne mreže, ali imena lokalnih mreža nisu dozvoljena",
    "'%value%' appears to be a DNS hostname but the given punycode notation cannot be decoded" => "'%value%' izgleda kao DNS ime poslužitelja ali zadanu 'punycode' notaciju nije moguće dekodirati",

    // Zend_Validate_Iban
    "Unknown country within the IBAN '%value%'" => "Nepoznata zemlja unutar IBAN-a '%value%'",
    "'%value%' has a false IBAN format" => "'%value%' ima neispravan IBAN format",
    "'%value%' has failed the IBAN check" => "'%value%' nije prošlo IBAN provjeru",

    // Zend_Validate_Identical
    "The two given tokens do not match" => "Zadane vrijednosti se ne podudaraju",
    "No token was provided to match against" => "Nije zadano vrijednost s kojom se treba usporediti",

    // Zend_Validate_InArray
    "'%value%' was not found in the haystack" => "Vrijednost '%value%' nije pronađena u polju",

    // Zend_Validate_Int
    "Invalid type given, value should be string or integer" => "Neispravan tip, vrijednost bi trebala biti niz ili cijeli broj",
    "'%value%' does not appear to be an integer" => "'%value%' ne izgleda kao cijeli broj",

    // Zend_Validate_Ip
    "Invalid type given, value should be a string" => "Neispravan tip, vrijednost mora biti niz",
    "'%value%' does not appear to be a valid IP address" => "'%value%' ne izgleda kao ispravna IP adresa",

    // Zend_Validate_Isbn
    "Invalid type given, value should be string or integer" => "Neispravan tip, vrijednost mora biti niz ili cijeli broj",
    "'%value%' is not a valid ISBN number" => "'%value%' nije ispravan ISBN broj",

    // Zend_Validate_LessThan
    "'%value%' is not less than '%max%'" => "'%value%' nije manje od '%max%'",

    // Zend_Validate_NotEmpty
    "Invalid type given, value should be float, string, array, boolean or integer" => "Neispravan tip, vrijednost mora biti realni broj, niz, polje, cijeli broj ili 'boolean'",
    "Value is required and can't be empty" => "Vrijednost ne smije biti prazna",

    // Zend_Validate_PostCode
    "Invalid type given. The value should be a string or a integer" => "Neispravan tip. Vrijednost mora biti niz ili cijeli broj",
    "'%value%' does not appear to be a postal code" => "'%value%' ne izgleda kao poštanski kod",

    // Zend_Validate_Regex
    "Invalid type given, value should be string, integer or float" => "Neispravan tip, vrijednost mora biti niz, cijeli ili realni broj",
    "'%value%' does not match against pattern '%pattern%'" => "'%value%' se ne podudara sa uzorkom '%pattern%'",
    "There was an internal error while using the pattern '%pattern%'" => "Došlo je do interne pogreške prilikom korištenja uzorka '%pattern%'",

    // Zend_Validate_Sitemap_Changefreq
    "'%value%' is not a valid sitemap changefreq" => "'%value%' nije ispravna vrijednost za sitemap 'changefreq'",
    "Invalid type given, the value should be a string" => "Neispravan tip, vrijednost mora biti niz",

    // Zend_Validate_Sitemap_Lastmod
    "'%value%' is not a valid sitemap lastmod" => "'%value%' nije ispravna vrijednost za sitemap 'lastmod'",
    "Invalid type given, the value should be a string" => "Neispravan tip, vrijednost mora biti niz",

    // Zend_Validate_Sitemap_Loc
    "'%value%' is not a valid sitemap location" => "'%value%' nije ispravna lokacija za 'sitemap'",
    "Invalid type given, the value should be a string" => "Neispravan tip, vrijednost mora biti niz",

    // Zend_Validate_Sitemap_Priority
    "'%value%' is not a valid sitemap priority" => "'%value%' nije ispravna vrijednost za sitemap 'priority'",
    "Invalid type given, the value should be a integer, a float or a numeric string" => "Neispravan tip, vrijednost mora biti cijeli broj, realni broj ili niz znamenki",

    // Zend_Validate_StringLength
    "Invalid type given, value should be a string" => "Neispravan tip, vrijednost mora biti niz",
    "'%value%' is less than %min% characters long" => "'%value%' ima manje od %min% znaka",
    "'%value%' is more than %max% characters long" => "'%value%' ima više od %max% znakova",
);
