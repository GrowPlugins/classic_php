# Classic.php
A library of project agnostic helper classes for simplifying every-day tasks. Classic.php classes are focused on specific tasks and iplementation-generic, which makes them great for use in virtually any project. These classes are primarily intended to reduce the need to code and recode the same every-day algorithms in various projects. Each class is relatively independent, so you can include only those you need, and not the ones you don't. By focusing on every-day tasks, this collection remains simple to pick up and start using, while remaining relevant to the task at hand.

Official Version: 0.1.2

## Requirements
* PHP >= 7.4 (Suggested: PHP >= 8.0)

## Class List
Classes found in Classic.php include:

* Database Helper Classes
  * MySQLPDO (classic_php/databases/mysql_pdo.php)
  * MySQLPDO_Read (classic_php/databases/mysql_pdo_read.php)
  * MySQLPDO_Write (classic_php/databases/mysql_pdo_write.php)
* Data Type Specific Helper Classes
  * ArrayProcessing (classic_php/data_types/array_processing.php)
* Miscellaneous Helper Classes
  * ErrorHandling (classic_php/misc/error_handling)

## Namespace Explanation
This project currently uses versioned namespaces in order to distinguish between different versions of the library being run at the same time in the same PHP environment. This is especially helpful for WordPress plugins. For example, two WordPress plugins may use different versions of Classic.php on the same website. Using versioned namespaces prevents namespace collisions and race conditions (where one version may be used or the other, but not both, although both plugins assume their version is the one being used). In order to use this convention and still keep up with the PHP standards (PSR-4), we put all of the library code within a folder under the same name as the sub-namespace. Therefore when transitioning from one version of the library to the next, it is necessary to update the name of the folder where all of the code is found, and also to do a recursive search and replace to update all of the namespace declarations throughout the code.

Please note that while the version number is found in the namespace and the project folder, no version of the library project should contain past versions in separate folders. The version is only meant to distinguish between different versions of the library, not to include copies of any past versions in the library.

## Improvements
If you have any suggestions for new functionality which could reduce code duplication across many different kinds of projects, please create a new issue.