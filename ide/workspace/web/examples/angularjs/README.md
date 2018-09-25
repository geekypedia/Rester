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