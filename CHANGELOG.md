# CHANGELOG
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## UNRELEASED
### Added
-  add some useful composer scripts
-  introduce strict types
### Changed
-  bump PHP to version 8+
-  bump Symfony components to version 5
-  bump all the other dependencies to te last available version
-  move to Susina organization
-  adhere to [Susina Coding Standard](https://github.com/susina/coding-standard)
### Removed
-  remove `phpDocumentor/ReflectionDocBlock` in favour of `gossi/docblock` which is lighter and easier to use
-  remove `symfony/simple-phpunit` in favour of `phpunit/phpunit`

## 4.1.0 (2018-06-25)
### Added
-  added support for Symfony 4+
-  allowed `true` and `false` type-hint
-  added support for php7 return types
### Dropped
-  dropped support for PHP 7.0

## 4.0.16 (2018-04-04)
### Fixed
 - fixed @see tag support
 
## 4.0.15 (2018-02-22)
### Added
-  added extended support for the @see tag

## 4.0.14 (2018-02-05)
### Fixed
-  fixed type null for nullable type parameters not added as type (PHP 7)

## 4.0.13 (2018-01-10)
### Added
-  implemented option to sort properties, methods, constants, traits and interfaces

### Fixed 
-  fixed class properties display
 
## 4.0.12 (2017-12-31)
###Added
- added support for variadics

## 4.0.11 (2017-12-20)
### Fixed
-  fixed "0" as default value not shown

## 4.0.10 (2017-11-08)
### Fixed
-  fixed cache bug
 
## 4.0.9 (2017-10-28)
### Fixed
-  prevented scope collision in Sami config file
-  fixed wrong variable using in NodeVisitor

## 4.0.8 (2017-09-05)
### Fixed
-  fixed counting of non-countable versions for PHP 7.2

## 4.0.7 (2017-09-05)
### Changed
-  made Sami skip anonymous classes
-  made sure VersionCollection::$versions is always an array
### Fixed
-  fixed Sami for PHP 7.2

## 4.0.6 (2017-06-08)
### Fixed
-  corrected docblock based method parameters

## 4.0.5 (2017-06-06)
### Fixed
-  corrected node visitor to correctly resolve hints to class parameters

## 4.0.4 (2017-05-31)
### Added
-  made it possible to copy namespace from the UI

## 4.0.3 (2017-05-05)
### Added
-  added @todo tag support
### Changed
-  switched to Markdown Extra instead of Markdown
### Fixed
-  fixed Tree parser sets links for namespaces containing sub namespaces

## 4.0.2 (2017-04-18)
### Added
-  added `$this` as class name alias for hints

## 4.0.1 (2017-03-24)
### Fixed
-  fixed parsing nullable types with php 7.1

## 4.0.0 (2017-01-05)
### Added
- added PublicFilter (same implementation as the old DefaultFilter in 3.x)
### Changed
-  changed DefaultFilter to included protected methods and properties
-  upgraded to PHPParser 3.0 (removed support for 2.x)
-  upgraded Symfony to 3.-
### Fixed
-  fixed version switcher
-  fixed parsing of @property tags in DocBlocks
### Removed
-  removed SymfonyFilter
-  removed support for Twig 1.x
-  removed support for PHP 5.x

## 3.3.0 (2016-06-07)
### Added
-  added support for the deprecated tag
### Removed
-  removed extra whitespace in generated HTML
-  removed usage of PHP reflection to determine if a class is internal

## 3.2.3 (2016-05-12)
### Fixed
-  fixed trait support when using filters

## 3.2.2 (2016-05-11)
### Changed
-  switched to phpDocumentor's parser for "@property" tag parsing

## 3.2.1 (2016-01-22)
### Fixed
-  fixed type hints when using a FQCN

## 3.2.0 (2016-01-19)
### Added
-  added a link to class methods if a remote repository is configured
-  added GitLab support
-  added BitBucket support
-  improved performance (a lot)
### Fixed
-  fixed --force option (again)
-  fixed Windows support

## 3.1.0 (2015-08-30)
### Added
-  improved parsing performance
### Fixed
-  fixed --force flag
-  fixed cache invalidation
-  fixed method doccomments on inherited classes when using caching
-  fixed visibility issue on methods and properties
### Removed
-  removed usage of Twig deprecated features

## 3.0.8 (2015-08-13)
### Added
-  added support for Twig 1.x and 2.x

## 3.0.7 (2015-07-11)
### Added
-  added responsive meta tags

### 3.0.6 (2015-06-28)
### Added
-  added "View source" link
### Fixed
-  fixed Windows \ vs / issue
-  fixed compatibility with PHPParser

## 3.0.3 (2015-04-08)
### Fixed
-  fixed links to php.net (to get the correct redirection)
### Removed
- removed deprecated usage of Symfony Yaml

## 3.0.2 (2015-02-21)
### Fixed
-  fixed error messages for methods and properties

## 3.0.1 (2015-02-18)
### Fixed
-  fixed command exit code when some parsing error occur to 64
-  fixed parsing error display when using --no-ansi
-  fixed tag parsing when the value is already an array

## 3.0.0 (2015-02-17)
### Added
-  added trait properties/methods to the class detail page
### Changed
-  made phpdocs available when deciding to process classes/methods/...
-  changed the default theme to use Twitter bootstrap
-  upgraded to Pimple 3.0
-  upgraded to PHP Parser 1.0

## 2.0.0 (2014-06-25)
### Changed
-  switched to a phar file as the recommended way to install Sami 
-  upgraded to Pimple 2.0

## 1.4 (2014-06-25)
### Changed
- allowed permalinks in the frames interface via the URI fragment
### Fixed
-  fixed a bunch of typos
-  fixed CLI when passing a directory as a config file
-  fixed missing project title in generated documentation

## 1.3 (2013-11-30)
### Added
-  added a check for non-clean repositories to avoid losing changes
-  added trait support
- added forwarding of default_opened_level configuration parameter from Pimple to Project
### Changed
-  updated the Markdown library used internally
### Fixed
-  fixed deep inheritance
  
## 1.2 (2013-09-27)
### Added
- added more valid PHP built-in types
### Changed
-  reworked the internals to make them more decoupled and reusable
### Fixed
-  fixed support for PHPParser 0.9.1

## 1.1 (2013-08-04)
### Added
-  added support for @property tag for 'magic' properties
-  added support for multiple visitors that modify a class
-  added Markdown support
-  persisted errors in the store for later retrieval
### Fixed
-  fixed js bug when inside an iframe

## 1.0 (2013-04-05)
- first stable release

## 0.8 (2012-05-15)
- initial Open-Source version
