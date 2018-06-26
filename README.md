# Simple API for Front Accounting

[![Build Status](https://travis-ci.org/cambell-prince/FrontAccountingSimpleAPI.svg?branch=master)](https://travis-ci.org/cambell-prince/FrontAccountingSimpleAPI)

I needed some basic integration functions to another software and decided to create this REST API and contribute to the Front Accounting community. I hope you find it usefull!

## Installation

*DO NOT* use git to clone this repo into your FrontAccounting modules folder unless you are a developer.

*DO* download the [latest release](https://github.com/andresamayadiaz/FrontAccountingSimpleAPI/releases/latest) in either zip or tgz and unpack into a folder such as .../modules/api.

## API Quick Start

1. Just copy the files into the modules directory under a folder called "api".
2. OPTIONAL: To test your installation Edit the file util.php and change the $company, $username and $password variables so you can test. Use it at your own risk, to provide login from another software you need to send X-COMPANY, X-USER and X-PASSWORD headers in the request and the API will use those credentials, if they're wrong you will get a nice message saying "Bad Login"
3. Try to access the API, for example, try the Items Category List, type this on your explorer: http://YOUR_FA_URL/modules/api/category/ You will see a JSON response with all you're items categories, if not check your credentials in the util.php file, or the X headers if set in the client.

## Documentation

See the [API Documentation](http://andresamayadiaz.github.io/FrontAccountingSimpleAPI/) for descriptions of each endpoint.

### Methods

The following API endpoints have been implemented:

- Sales
- Customers
- Items / Inventory
- Items Categories
- Suppliers
- Inventory Movements
- Locations
- Tax Groups
- Tax Types
- Bank Accounts
- GL Accounts
- GL Account Types.
- Journal

Some of them have not been tested yet so be carefull.

## How to Help

Report issues you find in our GitHub Issue Tracker. Please report with as much detail as you can. Simply saying "It doesn't work" will gain you sympathy, but not a lot else.

Want to contribute code? Go right ahead, fork the project on GitHub, pull requests are welcome. Note that we're trying to follow the [PSR-2 Coding Style Guide](https://www.php-fig.org/psr/psr-2/).

## Contact

Any question about this you can always contact me: andres.amaya.diaz@gmail.com