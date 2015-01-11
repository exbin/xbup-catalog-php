XBUP: eXtensible Binary Universal Protocol
==========================================

The XBUP Project aims to design and to provide an open-source support for unified general binary data representation format.

This should provide following advantages:

 * Advanced Data Structures - Unified structure should allow to combine various types of data together
 * Efficiency - Optional compression and encryption on multiple levels should allow effective representation of binary data
 * Flexibility - General framework should provide data transformations/processing and compatibility issues solving capability
 * Comprehensibility - Catalog of data types, metadata, relations and abstraction should allow better understanding of data

Homepage: http://xbup.org  
Project page: http://sourceforge.net/projects/xbup  

This repository contains PHP implementation of the catalog browser.

Please note, that catalog implementation in PHP is very ugly temporary solution and is supposed to be replaced by Java implementation.

Installing
----------

Catalog is suppose to run on LAMP server:

  * Apache http://httpd.apache.org/download.cgi
  * PHP http://www.php.net/downloads.php
  * MySQL http://dev.mysql.com/downloads/

License
-------

Project uses various libraries with specific licenses and some tools are licensed with multiple licenses with exceptions for specific modules to cover license requirements for used libraries.

Main license is: GNU/LGPL (see gpl-3.0.txt AND lgpl-3.0.txt)  
License for documentation: GNU/FDL (see doc/fdl-1.3.txt)  
