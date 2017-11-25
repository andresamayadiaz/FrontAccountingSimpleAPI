# Simple API for Front Accounting

[![Build Status](https://travis-ci.org/cambell-prince/FrontAccountingSimpleAPI.svg?branch=master-upstream)](https://travis-ci.org/cambell-prince/FrontAccountingSimpleAPI)

I needed some basic integration functions to another software and decided to create this REST API and contribute with Front Accounting team.
Hope you find it usefull !!

## Installation

*DO NOT* use git to clone this repo into your FrontAccounting modules folder unless you are a developer.

*DO* download the [latest release](https://github.com/cambell-prince/FrontAccountingSimpleAPI/releases/latest) in either zip or tgz and unpack into a folder such as .../modules/api.

## API Quick Start

1. Just copy the files into the modules directory under a folder called "api" or anything you want.
2. Edit the file util.php and change the $company, $username and $password variables so you can test. Use it at your own risk, to provide login from another software you need to send X-COMPANY, X-USER and X-PASSWORD headers in the request and the API will use those credentials, if they're wrong you will get a nice message telling "Bad Login"
3. Try to access the API, for example, try the Items Category List, type this on your explorer: http://YOUR_FA_URL/modules/api/category/ You will see a JSON with all you're items categories, if not check the util.php file.

## Methods

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

Some of them have not been tested yet so be carefull.

## How to Help

Report issues you find in our GitHub Issue Tracker. Please report with as much detail as you can. Simply saying "It doesn't work" will gain you sympathy, but not a lot else.

Want to contribute code? Go right ahead, fork the project on GitHub, pull requests are welcome.
## Contact

Any question about this you can always contact me: andres.amaya.diaz@gmail.com