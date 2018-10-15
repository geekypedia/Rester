Setup
-----

1. Open web based code editor: <a href="http://localhost:8080/ide" target="_blank">http://localhost:8080/ide</a> 
2. Load 'api' project
3. Open 'index.php'
4. Uncomment the following lines 
```php
enable_simple_auth($excluded, false);
$check_request_authenticity=true;
enable_simple_saas($excluded, $check_request_authenticity, true);
enable_files_api();
```
5. Load the 'web' project
6. Copy the content from 'seed.sql' file
7. Open web based DB admin tool: <a href="http://localhost:8080/db" target="_blank">http://localhost:8080/db</a> 
8. Paste the content of seed.sql into Sql Command and execute it.
9. Right-click the 'examples' folder in the IDE and click on 'preview' OR directy hit the following URL in your browser: <a href="http://localhost:8080/ide/workspace/web/examples" target="_blank">http://localhost:8080/ide/workspace/web/examples</a> 

Default Credentials
-----

Based on the 'seed.sql' there will be 3 default users

```
username: superadmin@example.com
role: superadmin
password: superadmin

username: admin@example.com
role: admin
password: admin

username: user@example.com
role: user
password: user
```

Roles
-----

#### superadmin

Can create new organizations, approve their licenses and make changes to the validity, reset password of the admin account.

By default $check_request_authenticity = true, so a superadmin can control licensing of an organization but can not see the actual data of that organization, which is the core requirement of SaaS.
However, for some reason, if you want superadmin to see everything you can do it by setting $check_request_authenticity = false.

#### admin

Can create new users under organizations and reset their passwords. Can access Administration section in menu.

#### user

Can use the application

Other Settings
-----

You can control the application settings from the following file

```JavaScript
app/config/settings.js
```

#### enableSaaS

This is turned on by default. If you turn it off, you will not be able to utilize the SaaS features of managing licenses and validity of an organization.
All the users will be seen as part of the same organization. It is rather recommended to keep it on, and use only one organization if you don't want your application to be SaaS based.

#### openRegistration

This if turned off by default. If you turn it on, you will see a 'Register' link under the login screen. Based on your SaaS settings either 'Register Organization' or 'Register User' functionality will be enabled.

Legacy Mode
-----
If you want to host this application on shared hosting providers such as 000webhost, they do not allow PUT and DELETE methods. In that case, just turn on legacyMode in app/config/settings.js, and also enable LEGACY_MODE in API project. All the PUT and DELETE requests will turn into POST requests.

Sample Code
-----
Please take a look at app/modules/tasks as a reference to write your own code.

Easiest way to add your own components is
1. Go to the Database Administration Tool http://localhost:8080/db and add table for your module. (For example if you wish to create a screen to manage employees, you would create an 'employees' table).
2. Adding a table to the database automatically creates relevant REST APIs. The route path of the API will be same as of the table name. (For example, in this case it will be /api/employees).
3. Go to app/config/routes.js and add an entry for the route under 'easyRoutes'.(For example, in this case it would be 'employees').
4. Go to app/config/menu.js and add an item under second array element.
For example, in this case add the following item
```
{action: 'employees', icon: 'people',text: 'Employees'}
```
The 'action' should always match the name of the API/table. You can use any 'icon' from [Materialize CSS Icons](https://materializecss.com/icons.html).
The menu.js will look like the following
```JavaScript
...
        {
            header: '',
            showHeader: false,
            showSeparator: false,
            items: [
        	    {action: 'tasks', icon: 'assignment_turned_in',text: 'Tasks'},
        	    {action: 'search', icon: 'search',text: 'Search'},
        	    {action: 'reports', icon: 'pie_chart',text: 'Reports'},
        	    {action: 'alerts', icon: 'alarm',text: 'Alerts'},
        	    {action: 'employees', icon: 'people',text: 'Employees'}
	        ],
	        allowedRoles: ['user', 'admin']
        },
...
```
5. Copy the 'app/modules/tasks' to 'app/modules/your-module-name'. In this case it will be 'app/modules/employees'. This is a boilerplate code and you will get all the CRUD operations along with pagination and loader.
6. See the comments in 'app/modules/your-module-name/controller.js'

Note: You can always go for customized code, and not follow the above rules. So in that case, you will start with 'customRoutes' in app/config/routes.js
