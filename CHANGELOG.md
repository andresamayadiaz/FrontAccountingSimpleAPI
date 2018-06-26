# CHANGELOG

#### 26 June 2018

- Add full CRUD for exchange rates under a new /exchangerates endpoint.

#### 23 June 2018

- Change README.md API documentation to point to http://andresamayadiaz.github.io/FrontAccountingSimpleAPI/

#### 22 June 2018

- Add full CRUD for Journal entry, update, and delete (void).

#### 21 June 2018

- Add full CRUD for Bank Accounts under bankaccounts endpoint.
- Fix formatting of code under src/ and test/ to conform to [PSR-2](https://www.php-fig.org/psr/psr-2/)

#### 20 June 2018

- Add full CRUD for GLAccounts under glaccounts endpoint.
- Add CRUD for Dimensions under dimensions endpoint.
- Add static api generator and sample documentation.

#### 19 June 2018

- Core VARLIB_PATH and VARLOG_PATH inclusions added [see this commit](https://github.com/FrontAccountingERP/FA/commit/4a37a28c49bf900dcc370fd3f21186cedcd632c9).
- TaxType: Added getById (Apmuthu 24 Nov 2017).
- Sales: Fix #32 Sales transactions Total not returned.
- Stock Adjust: Added unit test.
- Stock Adjust: Return now encoded as json msg.
- Stock Adjust: Missing argument $info fixed (Apmuthu 23 Apr 2018).
- Stock Adjust: add_stock_adjustment parameters fixed for FA 2.4 changes in API (Apmuthu, justapeddler 19 Apr 2018).
- Translated Spanish comments to English (Apmuthu 18 Nov 2017).

#### 27 November 2017

- Refactor tests to ensure consistency in POST and PUT api.
  Added tests/Crud_Base.php
- Add api_ensureAssociateArray to remove the numeric index elements that come from the Front Accounting functions.
- The category end point now follows the database schema more closely for property names.
- The customers end point now follows the database schema more closely for property names.

#### 23 November 2017

- Added support for requests sent using Content-Type: application/json
  The body is presumed to be in JSON format and converted appropriately.

#### 17 November 2017

- Updated API to support Front Accounting version 2.4.x
- Added PHP Unit tests
- Added Travis CI build

#### 6 September 2014
Thanks to Cambell Prince

- Added composer.json and dependency on Slim
- Improved error presentation for xdebug users.
- Changed expected headers to uppercase.
- Switch to use composer installed Slim
- Switch to use Slim installed via composer.
- Remove hard coded path 'api' and simplify includes.

#### 17 September 2014
Thanks to Salman Sarwar

- Bug Fix in inventory.inc

#### 14 July 2013:
- Added .htaccess so you can now use API URL's without index.php, examples:
  (Thanks to Christian Estrella)
    OLD: GET http://mysystem.com/api/index.php/locations/
    NEW: GET http://mysystem.com/api/locations/

- Added Pagination to GET methods, it used to return all entries, now is per page, under index.php it has define("RESULTS_PER_PAGE", 2); that defines how many entries you will get per page, if you dont establish a page on the request you will get the first page. (Thanks to Christian Estrella)
    OLD Request: GET http://mysystem.com/api/index.php/locations/
    OLD Response: ALL LOCATIONS
    
    NEW Request 1: GET http://mysystem.com/api/index.php/locations/
    NEW Response 1: First Page of Locations
    
    NEW Request 2: GET http://mysystem.com/api/index.php/locations/?page=5
    NEW Response 2: Page 5 of Locations

- Added Sales Transactions Methods for Quotes, Sales Orders, Deliveries, Invoices (GET, PUT, POST)
    NOTE: This changes hasn't been tested deeply, might have some bugs

#### 14 June 2013:
- Added POST /locations/ To Add A Location Thanks to Richard Vinke

