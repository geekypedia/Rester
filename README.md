#RESTer

RESTer is a fork of moddity/Rester
	
##Requeriments

- PHP 5.4+ & PDO
- MySQL

##Features

- Create an API in 5 minutes. Just go to config.php and provide correct database credentials.
- You can run the system simply using plain old PHP server (php -S 0.0.0.0:8080) and browse http://localhost:8080 using your browser.
- MySQL Adminer bundled at http://localhost:8080/admin/ Log on using correct database credentials and manage your database directly from within your browser.
- Auto-generation of swagger-ui documentation on http://localhost:8080/docs/ 
- Codeiad IDE bundled at http://localhost:8080/ide/ . Change your code on the fly from within your browser. See the output directly at http://localhost:8080/ide/workspace/your-project-folder
- Two projects are already created in the IDE. 'web' and 'api'. Any APIs defined in api/index.php will be automatically loaded.
- Default credentials for IDE are as below:
	username: admin
	password: admin
- MySQL Relation support
- File upload support
- Custom API functions
- Filters


##Installation

Edit `config.php`, here are some examples:

```php
/** The API Version */
define('API_VERSION', "1.0.0");

/** Database credentials */
define('DBHOST', 'localhost');
define('DBNAME', 'mydb');
define('DBUSER', 'dbuser');
define('DBPASSWORD', 'dbpassword');

/** Enable logging on error.log */
//define('LOG_VERBOSE', true);

/** Path where uploads */
define('FILE_UPLOAD_PATH', 'uploads');

```

##API Design

The actual API design is very straightforward and follows the design patterns of the majority of APIs.

	(C)reate > POST   /table
	(R)ead   > GET    /table[/id]
	(U)pdate > PUT    /table/id
	(U)pdate > POST   /table/id
	(D)elete > DELETE /table/id

To put this into practice below are some example of how you would use the Rester API:

	# Get all rows from the "customers" table
	GET http://api.example.com/customers/

	# Get a single row from the "customers" table (where "123" is the ID)
	GET http://api.example.com/customers/123

	# Get 50 rows from the "customers" table
	GET http://api.example.com/customers/?limit=50

	# Get 50 rows from the "customers" table and skip first 50 rows
	GET http://api.example.com/customers/?limit=50&offset=50

	# Get 50 rows from the "customers" table ordered by the "date" field
	GET http://api.example.com/customers/?limit=50&order=date&orderType=desc
	
	# Get all the customers named LIKE Tom; (Tom, Tomato, Tommy...)
	GET http://api.example.com/customers/?name[in]=Tom

	# Create a new row in the "customers" table where the POST data corresponds to the database fields
	POST http://api.example.com/customers

	# Update customer "123" in the "customers" table where the PUT data corresponds to the database fields
	PUT http://api.example.com/customers/123
	POST http://api.example.com/customers/123

	# Delete customer "123" from the "customers" table
	DELETE http://api.example.com/customers/123

Please note that `GET` calls accept the following query string variables:

- `order` (column to order by)
  - `orderType` (order direction: `ASC` or `DESC`)
- `limit` (`LIMIT x` SQL clause)
  - `offset` (`OFFSET x` SQL clause)
- `parameter[in]` (LIKE search)
- `parameter[gt]` (greater than search)
- `parameter[lt]` (less than search)
- `parameter[ge]` (greater or equals search)
- `parameter[le]` (less or equals search)
- `orFilter` (or contition for the multiple parameters)


##Changelog

- **beta** 

##Credits
RESTer adds enhancements over moddity/Rester (https://github.com/moddity/Rester)
Rester is a nearly complete rewrite of [ArrestDB](ArrestDB: https://github.com/alixaxel/ArrestDB) with many additional features.
ArrestDB is a complete rewrite of [Arrest-MySQL](https://github.com/gilbitron/Arrest-MySQL) with several optimizations and additional features.

##License (MIT)

Copyright (c) 2017 Geekypedia (http://www.geekypedia.net)
