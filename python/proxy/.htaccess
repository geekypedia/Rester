DirectoryIndex disabled
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteRule ^$ http://127.0.0.1:49999/ [P,L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ http://127.0.0.1:49999/$1 [P,L]
</IfModule>
<Limit GET POST PUT DELETE>
  Allow from all
</Limit>
<Files ~ "\.config$">
Order allow,deny
Deny from all
</Files>
<Files ~ "\.key$">
Order allow,deny
Deny from all
</Files>
