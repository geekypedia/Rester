![pRESTige Framework](http://prestigeframework.com/wp-content/uploads/2018/09/logo.png)
=====

Introduction
-----

pRESTige is basically a RAD (Rapid Application Development) toolset that allows application development completely from within your web browser.

It mainly comprises of an API engine that projects your MySQL database as a fully working collection of RESTful APIs which are compliant with OpenAPI Specifications (Swagger). You can just plug-in your existing database by providing a connection string, and immediately you will get fully featured RESTful APIs, along with documentation generated in Swagger. It supports OpenAuth and also provides a token based mechanism for securing your APIs. It also provides built-in login APIs to authenticate and generate token. It provides built-in file upload APIs. It provides embedded IDE, DB Management Tool and terminal and you can run all of these from browser. 

Best part of this is, it has got a very simple architecture, so it is compatible to be hosted as-is on shared hosting environments, even on the free ones like FreeHosting.com, GoogieHost and 000webhost. We have also tried it on cheaper hosts like HostBudget.com, BingLoft, Flaunt7, KatyaWeb. So, all you need is your grandpa's laptop, a browser and internet.

This is your silver bullet for Rapid Application Development in web browser. 

Features
-----

+ Automatically and dynamically converts MySQL tables to RESTful APIs without writing a single line of code.
+ All relations within tables are maintained in APIs.
+ Strong query engine. Query your APIs as you would do in SQL.
+ Changes in the database are immediately reflected in APIs, without having to restart the process.
+ Changes in the APIs are immediately reflected in API documentation without having to regenerate anything.
+ Test your APIs without having to install any plug-ins in your browser.
+ Embedded light-weight database management directly from your browser. You can make changes in the tables without having to reply on any desktop tool.
+ Embedded code editor. Develop your application directly from your browser. See live preview.
+ Embedded terminal. Run linux commands directly from your browser.
+ Built-in authentication and token generation APIs
+ Built-in file upload APIs
+ Support for OAuth
+ Support for Shared Hosting (Except the terminal component)


Installation
-----

It is extremely easy get quickly up and running using pRESTige!

`git clone https://github.com/geekypedia/prestige`

`cd prestige`

`php -S 0.0.0.0:8080`

Now just open the following link to open the dashboard: <a href="http://localhost:8080/api" target="_blank">http://localhost:8080/api</a>

Open the 'API Configuration' to connect the system to a database.

```
username: admin
password: admin
```


You can use the following endpoints to use the system.

|Component						| URL									|
|-------------------------------|---------------------------------------|
|Dashboard							| <a href="http://localhost:8080/" target="_blank">http://localhost:8080</a> |
|API Configuration				| <a href="http://localhost:8080/api/configure/" target="_blank">http://localhost:8080/api/configure</a> |
|API							| <a href="http://localhost:8080/api/" target="_blank">http://localhost:8080/api</a> |
|API Documentation				| <a href="http://localhost:8080/api/docs/" target="_blank">http://localhost:8080/api/docs</a> |
|API Testing Tool				| <a href="http://localhost:8080/api/test/" target="_blank">http://localhost:8080/api/test</a> |
|Database Administration		| <a href="http://localhost:8080/db/" target="_blank">http://localhost:8080/db</a> |
|Code Editor					| <a href="http://localhost:8080/ide/" target="_blank">http://localhost:8080/ide</a> |
|Terminal						| <a href="http://localhost:8080/terminal/" target="_blank">http://localhost:8080/terminal</a> |
|HTML5 Builder						| <a href="http://localhost:8080/builder/" target="_blank">http://localhost:8080/builder</a> |


Prerequisites
-----

You need PHP 5.4+ to run the application. You will also need the have the access to a MySQL server database. You can run the following commands to make sure all php dependencies are taken care of. The following commands use PHP 7.0. You can modify them to match your version.

`sudo apt-get install -y php7.0 php7.0-cli php7.0-common php7.0-mbstring php7.0-gd php7.0-intl php7.0-xml php7.0-mysql php7.0-mcrypt php7.0-zip`

If you wish to deploy it to Apache server, then you need to make sure that you run the follwing commands.

`sudo apt-get install -y libapache2-mod-php7.0 `

How do I use pRESTige?
-----

#### Run the application and configure it with DB

pRESTige Api Engine basically converts all of your MySQL database tables with relations into RESTful APIs. It is dynamic. It is a runtime. So once your API Engine is connected to the DB, any changes in the DB directly reflects in APIs.

So the first step is to provide a connection string to the API Engine. It requires username, password and database name. Rather than modifying any config file, you can directly do that from the application itself. You can one of the following command to run the application.

`php -S 0.0.0.0:8080`

OR

`./serve.sh`

You will see a dashboard from where you can control settings or launch the tools.

The first thing is to configure the database connection. Click on 'API Configuration' from the dashboard.

Put the default credentials (Note: You can always change these credentials by changing them in the Code Editor)

```
username: admin
password: admin
```


OR you can just go to the following URL. 

<a href="http://localhost:8080/api" target="_blank">`http://localhost:8080/api`</a>

When you are running this application for the first time, you will see prompt to provide your database credentials. Don't worry if you make a mistake here. You can always re-configure it by hitting the following URL again, or going to the dashboard and click on 'API Configuration'.

<a href="http://localhost:8080/api/configure" target="_blank">`http://localhost:8080/api/configure`</a>

#### Check the generated API docs and testing tool

Once you have provided the connection string, you can see your tables turned into API with full documentation at the following location: 

<a href="http://localhost:8080/api/docs" target="_blank">`http://localhost:8080/api/docs`</a>

The documentation is based on Swagger and you can use the same documentation tool to test out your APIs. But along with that, if you want a fully customizable tool, just hit this url.

<a href="http://localhost:8080/api/test" target="_blank">`http://localhost:8080/api/test`</a>

#### Manage and modify your database

If you don't have an existing database, and you just installed mysql and you are looking for a quick light-weight tool to manage your MySQL, you are in luck. pRESTige bundles an awsome open-source tool (Courtesy of Adminer) that you can use to create and manage your MySQL. Just hit this link. 

<a href="http://localhost:8080/db" target="_blank">`http://localhost:8080/db`</a>

#### Online code editor

Now that you can manipulate your DB directly from browser, and see its results reflected immediately on documentation, you would want to start coding your web application. For that, you would need an IDE. You don't need to download anything, just go here. 

<a href="http://localhost:8080/ide" target="_blank">`http://localhost:8080/ide`</a>

Put the default credentials (Note: You can always change these credentials in the Code Editor)

```
username: admin
password: admin
```

This is a fully featured IDE directly in your browser. 

We have created 2 projects for you already. One is 'web' and the other is 'api'.

##### Customize APIs

Readymade APIs are good but what if you want to create your own customized API? May be you want to call a stored procedure in your DB, or you just want to write an API that is not doing your regular CRUD. Just load the API project. There is a sample API written there. Create a copy of that API and modify whatever you want. By default, pRESTige will load anything that is written in the API project. If doesn't matter how many PHP file you create inside that project, it will load them all, so no need for you to do any kind of book keeping.

##### Start writing your Application

Inside the 'web' project, you would find some examples that you can use to learn how you can utilize existing frameworks such as angularjs to call the restful APIs and all.

By default, <a href="http://localhost:8080/" target="_blank">http://localhost:8080/</a> will redirct you to <a href="http://localhost:8080/ide/workspace/web/" target="_blank">http://localhost:8080/ide/workspace/web</a>. 

So whatever you create/modify inside the 'web' project can directly be tested in browser. If you deploy this toolset without any modification, you can actually make changes in live application code without any downtime. However, this scenario is most likely useful in development and beta phase only. When you are ready for production, you can simple copy the content inside your 'web' project to the root folder.

There are some samples in the 'examples' folder under 'web' project. You may explore them at: http://localhost:8080/ide/workspace/web/examples/

##### You can do more

This does not restrict you to 2 projects only. You can create as many as you want, and you can even preview your application by rightclicking any file and folder from editor and launching preview.

#### Online terminal

Last but not least, if you want to run linux terminal commands within your application directory, you can do so from the browser. Just go here. 

<a href="http://localhost:8080/terminal" target="_blank">`http://localhost:8080/terminal`</a>

The credentials to use terminal are same as that of IDE/Code Editor.

The terminal feature will usually not work in shared hosting environment, because they don't allow calling external processes from PHP. However, if you have hosted it on your own server, there won't be such restrictions. This will make it easy during development phase. 

What are the default credentials for API Configuration, IDE and Terminal?
-----
Username: admin
Password: admin

Both of them use the same set of credentials. You can change the credentials by logging into the IDE.

How do I query APIs?
-----

The actual API design is very straightforward and follows the design patterns of the majority of APIs.

	(C)reate > POST   /table
	(R)ead   > GET    /table[/id]
	(U)pdate > PUT    /table/id
	(U)pdate > POST   /table/id
	(D)elete > DELETE /table/id

To put this into practice below are some example of how you would use the pRESTige API:

	# Get all rows from the "customers" table
	GET http://localhost:8080/customers/

	# Get a single row from the "customers" table (where "123" is the ID)
	GET http://localhost:8080/customers/123

	# Get 50 rows from the "customers" table
	GET http://localhost:8080/customers/?limit=50

	# Get 50 rows from the "customers" table and skip first 50 rows
	GET http://localhost:8080/customers/?limit=50&offset=50

	# Get 50 rows from the "customers" table ordered by the "date" field
	GET http://localhost:8080/customers/?limit=50&order=date&orderType=desc
	
	# Get all the customers named LIKE Tom; (Tom, Tomato, Tommy...)
	GET http://localhost:8080/customers/?name[in]=Tom

	# Get count of the customers
	GET http://localhost:8080/customers/?count=true

	# Create a new row in the "customers" table where the POST data corresponds to the database fields
	POST http://localhost:8080/customers

	# Update customer "123" in the "customers" table where the PUT data corresponds to the database fields
	PUT http://localhost:8080/customers/123
	POST http://localhost:8080/customers/123

	# Delete customer "123" from the "customers" table
	DELETE http://localhost:8080/customers/123

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

How do I enable authentication?
-----

1. Open web based code editor: <a href="http://localhost:8080/ide" target="_blank">http://localhost:8080/ide</a> 
2. Load 'api' project
3. Open 'index.php'
4. Uncomment the call to 'enable_simple_auth($excluded);' function.
5. If you want to bypass any specific API you can pass it as parameter. For example, 'enable_simple_auth(array("GET your/api"));' will exclude GET your/api from authentication. Any other API will require you to pass auth token as api_key in header or query string. By default, the sample API 'GET hello/world' is always bypassed. Note: Some of the shared hosting providers do not allow headers with underscore. You may use 'api-key' in that case.
6. In order for the auth APIs to work, you need to have a 'users' table in your DB. The script to create this table is already mentioned in 'index.php'. You can copy this script and execute it in the DB Administration tool (<a href="http://localhost:8080/db" target="_blank">http://localhost:8080/db</a> )
7. Once you uncomment the enable_simple_auth call, all APIs and even Documentation will be protected. You will need to call 'POST users/login' to authenticate and generate a token.
   For first time, just create a record in 'users' table and write any random string as token. Use this token to access to access protected areas, or generate an actual token.

How do I generate token after enabling authentication?
-----
1. You need to have a username/email and password that matches a record in 'users' table in the database.
2. Open web based API Testing Tool: <a href="http://localhost:8080/api/test" target="_blank">http://localhost:8080/api/test</a> 
3. Make a POST request to 'http://localhost:8080/api/users/login'. Provide either username or email as parameter. Provide password as parameter.
4. On successful request, you will get a users object. The object should have a token. The token expires every 24 hour. Everyday at 00:00 hour, the old token will not work and you will need to call this API again to generate a new token.

What other users API are available after enabling authentication?
-----
```php
POST users/login
POST users/set-password
POST users/change-password
POST users/forgot-password
```

How do I use token?
-----
If authentication is enabled, you will get '401 Unauthorized' response when you call any of your API. You need to pass the value of the token as a header 'api_key' while calling your APIs.

Even the API documentation section will be protected. You can use the same token as api_key on the documentation screen.


What is SaaS Mode?
-----
In SaaS mode, the API engine automatically associates every table with a 'secret'. This secret is another 'token' similar to api_key. api_key makes sure you are authenticated, where as secret makes sure you only get to see data and users of your own organization.

So, in order to enable SaaS mode, you need to have an 'organizations' table in your database. This table will have 'org_secret' that will be unique for each organization.

Each user in the users table will have a 'secret' that matches the 'org_secret'. It is expected that whatever tables you create should also have a field called 'secret'.

Once you login using the default users/login api, you will get both 'api_key' and 'secret'. Then you need to pass secret along with api_key for all subsequent requests. api_key goes in header, but secret goes in querystring (GET/DELETE) or body (POST/PUT). Being part of body/querystring it is matched with the secret field in the database for each table, hence you always get results filtered by organization. 


How do I enable SaaS Mode?
-----

1. Open web based code editor: <a href="http://localhost:8080/ide" target="_blank">http://localhost:8080/ide</a> 
2. Load 'api' project
3. Open 'index.php'
4. Uncomment the call to 'enable_simple_saas($excluded, $check_request_authenticity, $enable_open_registrations);' function. (Authentication is a prerequisite, so make sure you have already enabled simple authentication mentioned in the point below!).
5. If you want to bypass any specific API you can pass it as parameter. For example, 'enable_simple_saas(array("GET your/api"));' will exclude GET your/api from organization permissions. Any other API will require you to pass secret in body or query string.
6. In order for the SaaS APIs to work, you need to have a 'organizations' table in your DB. The script to create this table is already mentioned in 'index.php'. You can copy this script and execute it in the DB Administration tool (<a href="http://localhost:8080/db" target="_blank">http://localhost:8080/db</a> )
7. Once you uncomment the enable_simple_auth call, all APIs will be protected by organization secret. You will need to call 'POST users/login' to authenticate and generate a secret.
   For first time, just create a record in 'organizations' table and write any random string as secret. You can use the sample scripts in index.php to do it.

Roles in SaaS Mode
-----

#### superadmin

Can create new organizations, approve their licenses and make changes to the validity, reset password of the admin account.

By default $check_request_authenticity = true, so a superadmin can control licensing of an organization but can not see the actual data of that organization, which is the core requirement of SaaS.
However, for some reason, if you want superadmin to see everything you can do it by setting $check_request_authenticity = false.

#### admin

Can create new users under organizations and reset their passwords. Can access Administration section in menu.

#### user

Have no specific permissions. Can use the rest of the APIs.


How can I create an API that can upload files to the server?
-----
You don't need to. There is a built in API for the same.

1. Open web based code editor: <a href="http://localhost:8080/ide" target="_blank">http://localhost:8080/ide</a> 
2. Load 'api' project
3. Open 'index.php'
4. Uncomment the call to 'enable_files_api();' function.
5. In order for the auth APIs to work, you need to have a 'files' table in your DB. The script to create this table is already mentioned in 'index.php'. You can copy this script and execute it in the DB Administration tool (<a href="http://localhost:8080/db" target="_blank">http://localhost:8080/db</a> )
6. You can use the API Docs to see the new Files API and you can test it out from there: <a href="http://localhost:8080/api/docs/" target="_blank">http://localhost:8080/api/docs</a> 

How do I create a custom API?
-----
1. Open web based code editor: <a href="http://localhost:8080/ide" target="_blank">http://localhost:8080/ide</a> 
2. Load 'api' project
3. Create a .php file under this project. You can keep it anywhere (direcly in project root folder or within a subfolder).
4. Register a new custom API as shown in the example below.

```php
$prestige->register("GET", "hello", "world", function($params=null){
	global $prestige;
	$value = $prestige->query("select 'world' as 'hello'"); //you can do any type of MySQL queries here.
	$prestige->showResult($value);
}, array(), "Hello World Api");
```

This will create a new API available at http://localhost:8080/api/hello/world

You can access GET or POST parameters with $params['parameter_name'].

$prestige Operations
-----
$prestige is a global variable that references the core API engine. You can use it to write middleware, interceptors and custom REST APIs. It has got some useful methods available for routine operations.

#### register($method, $route, $path, $handler, $required_parameters, $description)

Use this method to register a new custom API.

Example:
```php
$prestige->register("GET", "hello", "world", function($params=null){
	global $prestige;
	$value = $prestige->query("select 'world' as 'hello'"); //you can do any type of MySQL queries here.
	$prestige->showResult($value);
}, array(), "Hello World Api");
```

The actual API function is passed with variable $params. You can access querystring or post-body data from $params.
For example: $params['id'] or $params['email']

If you want to use other methods of $prestige, just refer to it as a global variable.

You can write any kind of PHP code here. You can also use the helper functions mentioned below. It is recommended to use showResult or showError to render appropriate result from API.

#### query($queryString)

200 OK with the result of executing the specified querystring against the database. It can be any kind of SQL statement such as SELECT, INSERT, UPDATE, DELETE or a CALL to any stored procedure, etc.

#### find($route, $filters = NULL, $match_any = false)

200 OK with the result of querying a particular REST API route by passing filter parameters. By default, all of the filter parameters will be matched. If you pass $match_any = true, then any of the filter parameter will be matched.

#### findOne($route, $id)

200 OK with the result

#### create($route, $object)

201 CREATED with the created object with id

#### update($route, $id, $object)

200 OK with the updated object

#### delete($route, $id)

200 OK with the deleted object

#### showResult($value)

200 OK with specified value

#### showError($errorCode, $message = NULL)

HTTP_ERROR. Exmaple 400, 404, 405, 422, 500. With optional message.

#### getURL($url, $params = null, $headers = null)

Get response from any 3rd party URL. Useful for integrating with various 3rd party platforms.

#### postURL($url, $payload = null, $headers = null)

Post data to any 3rd party URL. Useful for integrating with various 3rd party platforms.

#### prepareMail($template, $data)

Provide a tokenize template with mustache/angularjs like syntax, and replace it with actual data.

For example:
The output of the following program
```php
$template = "Hello {{username}}!";
$data["username"] = "batman";
echo $prestige->prepareMail($template, $data);
```
will be
```php
Hello batman!
```

#### sendMail($from, $to, $subject, $body, $smtp)

Send e-mail using SMTP.

```php
$from = "youremail@yourdomain.com";
$to = ["recepientsemail@theirdomain.com"];
$subject = "SUBJECT GOES HERE";
$body = "TEXT or HTML GOES HERE";
$smtp = array(
	"host": "smtp.yourdomain.com",
	"username": "YOUR USERNAME",
	"password": "YOUR PASSWORD",
	"proto": "tls",
	"port": 587
);
$prestige->sendMail($from, $to, $subject, $body, $smtp);
```


#### sendMailSparkPost($from, $to, $subject, $body, $api_key)

If you want to send e-mails from your code usually you use the SMTP send mail methods. However, in real world scenarios, most of shared hosting providers do not allow using that unless you pay them extra. If you use your private email account for this purpose, it is likely that providers like Google or Microsoft may block your account.

There are some good 3rd party services which allow us to send emails using their platform for free. One of them is SparkPost. You just need a domain/subdomain. It doesn't matter paid or free. All you need is a way to manage DNS entries for that particular domain/subdomain. Then you need to register an account with SparkPost, register your domain with them, do the necessary configurations and get an api key for sending mails.

```php
$from = "youremail@yourdomain.com";
$to = ["recepientsemail@theirdomain.com"];
$subject = "SUBJECT GOES HERE";
$body = "TEXT or HTML GOES HERE";
$api_key = "YOUR_SPARKPOST_API_KEY";
$prestige->sendMailSparkPost($from, $to, $subject, $body, $api_key);
```

#### uuid()

Returns a unique 32 characters identifier.

#### intersectString($str1, $str2)

Returns intersection between 2 strings.

#### where($array, $property_name, $where, $single = true, $only_return_keys = false)

Similar to SQL search. Find a matching record in an array where the provided value matches the value of the specified property of an object in an array.

The following will return first customer whose city is 'New York'
```php
$prestige->where($customers, 'city', 'New York');
```

The following will return all customers whose city is 'New York'
```php
$prestige->where($customers, 'city', 'New York', false);
```

The following will return only index of first customer in the $customers array whose city is 'New York'
```php
$prestige->where($customers, 'city', 'New York', true, true);
```

#### requestIsMobile()

Checks if request is being made from a mobile browser.

#### requestRoute()

Returns current route in the following format
```
METHOD route/path
```

Examples:
```
GET hello/world
POST users/login
```

#### encrypt($text, $key)

Encrypt text.

#### decrypt($text, $key)

Decrypt text.

#### generateCryptoKey()

Generate key to be used in encryption and decryption.

#### diff($obj1, $obj2)

Generate diff between two objects. Can be useful for generating audit logs.

Events
-----

On POST, PUT and DELETE events of the generated APIs, you can hook your own code by defining these functions as handlers of those events.

Convention for event handler function syntax:
```php
before_method_route($data)
on_method_route($result)
```

Example:
If you create a table named 'users' in the database, you get RESTful APIs at '/users'. The events for this endpoint would be as below:
```php
before_post_users($data)
before_put_users($data)
before_delete_users($data)
on_post_users($result)
on_put_users($result)
on_delete_users($result)
```

You can just define these events as functions in your API project and do additional stuff there.

Examples:
```php
function before_post_users($data){
	//Write code to check if $data['email'] contains prohibited domain names.
}


function on_post_users($result){
	//Write code to send an email to $result['email'] using send_email_sparkpost() method
}
```

Custom Events
-----

#### on_organization_registered($organization)

If you have enabled SaaS mode and open registrations, then you get /organizations/register API. When you call it to register an organization, you will get this event. You can use this event to notify end users to wait till they are activated.

#### on_user_registered($user)

If you have enabled auth mode and open registrations, then you get /users/register API. When you call it to register a user, you will get this event. You can use this event to notify end users with a welcome email.

#### on_organization_activated($organization, $user)

If you have enabled SaaS mode, then you get /organizations/activate API. When you call it and you want an event on activation or license change of an organization, probably to send an email, define this function in your api project.
$user will not be available if the event is fired after the user is already activated. This would be the case when you just want to change license or validity information.

#### on_forgot_password($email, $password)

If you have enabled Simple Auth, and you want to handle the event when somebody calls /users/forgot-password API, just define this function and send an email to the email address in the parameter, with the password!

#### on_set_password($email, $password)

If you have enabled Simple Auth, and you want to handle the event when somebody calls /users/set-password API, just define this function and send an email to the email address in the parameter, with the password!

#### on_change_password($email, $password)

If you have enabled Simple Auth, and you want to handle the event when somebody calls /users/change-password API, just define this function and send an email to the email address in the parameter, with the password!

#### on_login($user)

If you have enabled Simple Auth, then you get /users/login API. On successful login, you can hook on_login function to run your own code.

Middleware Functions
-----
You can define these function anywhere in your API project and it will be injected in the request pipe-line to perform specific actions.

#### request_headers_remove()

Sometimes, Your legacy applications might want to call your APIs and they are passing some extra paramters that the APIs are not expecting. This will force your APIs to return 405 - method not allowed error. This function is used to remove those extra headers, so your APIs will work fine even if any middleware in your legacy applications or infrastructure is passing on additional headers.

Example
```php
function request_headers_remove(){
	return array("custom-header-1", "custom-header-2");
}
```

Request Interceptors
-----
All PHP files under the API project will be automatically picked up by API engine. You can use this project to do configuration, write Custom APIs, Event Handlers and Middleware functions.

If you write any code that is not encapsulated in a function, it will be applied to all the requests. So you can use this behaviour to write interceptors.

Example: Write code to restrict request coming from certain IP addresses.

Localization
-----
If you want to register your localized text at a central location, you can do so by calling 'M'.

#### M($key, $message)

Registers a message.

#### M($key)

Fetches the message referred by $key.

#### M($key_message_pairs)

Register bulk messages.

#### M_load($tableName)

Load $key and $message from a database table. The table should have 'key' and 'message' columns.

#### M_load($tableName, $keyColumnName, $messageColumnName)

Load $key and $message from a database table. Specify the column names for key and message.

Credits
-----
pRESTige started as a fork of moddity/Rester. moddity/Rester was a nearly complete rewrite of [ArrestDB]. ArrestDB was a complete rewrite of [Arrest-MySQL].

It has reached a long way now after fixing existing bugs and adding more features in the original engine. Now it also bundles some more tool to get you productive quickly.

Along with the original fork, I have also bundled some open-source productivity tools - a browser based code editor (Codiad), database administration tool (Adminer), a REST API testing tool [REST Test Test](https://resttesttest.com/), and a web-based terminal [web-console](https://github.com/nickola/web-console).

I am thankful to all those people whose work directly or indirectly landed here and made creation of this Frankenstein possible.

License (MIT)
-----

Copyright (c) 2017 Geekypedia (http://www.geekypedia.net)
