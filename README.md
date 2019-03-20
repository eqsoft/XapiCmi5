ILIAS XapiCmi5 plugin
=============================

Copyright (c) 2018 internetlehrer GmbH

- Author:  Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
- Forum: 
- Bug Reports: http://www.ilias.de/mantis (Choose project "ILIAS plugins" and filter by category "XapiCmi5 Plugin")


Requirements
------------

Ubuntu / Debian 

Dependant on php7.x Version:
package: php7.x-curl

Extra Server Configuration
--------------------------

- Basic Authentification headers MUST be transparent in php environment. Using apache with php-fpm behind reverse proxy (p.e. nginx) needs an explicit declaration like this:

```
<FilesMatch \.php$>
		SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
        
        .....
        
</FilesMatch>
```

- The XapiProxy needs specific cors settings for preflight requests (see https://developer.mozilla.org/de/docs/Web/HTTP/CORS)

- for pure apache you can use the xapi/tpl.htaccess file. Copy and rename to .htaccess and allow .htaccess files to be parsed in that folder. For better performance it is recommanded to declare an extra directory directive in the apache conf for the PLUGIN/xapi* script with the preflight settings like this:

```
<Directory /WEBROOT_PATH/Customizing/global/plugins/Services/Repository/RepositoryObject/XapiCmi5/xapi>
    # set env var if request method is OPTIONS
SetEnvIf Request_Method OPTIONS setheader

# with AJAX withCredentials=false (cookies NOT sent)
Header set Access-Control-Allow-Origin "*" env=setheader                   
Header set Access-Control-Allow-Methods "POST, GET, PUT, OPTIONS, PATCH, DELETE" env=setheader 
Header set Access-Control-Allow-Headers "X-Accept-Charset,X-Accept,Content-Type,X-Experience-API-Version" env=setheader
#Header set Access-Control-Max-Age 600 env=setheader

RewriteEngine On                  
RewriteCond %{REQUEST_METHOD} OPTIONS 
#RewriteRule ^(.*)$ $1 [R=200,L,E=HTTP_ORIGIN:%{HTTP:ORIGIN}]]
RewriteRule ^(.*)$ blank.php [QSA,L]

# with AJAX withCredentials=true (cookies sent, SSL allowed...)
SetEnvIfNoCase ORIGIN (.*) ORIGIN=$1
SetEnvIf Request_Method OPTIONS setheader 
Header set Access-Control-Allow-Methods "POST, GET, PUT, OPTIONS, PATCH, DELETE" env=setheader 
Header set Access-Control-Allow-Origin "%{ORIGIN}e" env=setheader
Header set Access-Control-Allow-Credentials "true" env=setheader
Header set Access-Control-Allow-Headers "authorization,X-Accept-Charset,X-Accept,Content-Type,X-Experience-API-Version" env=setheader
#Header set Access-Control-Max-Age 600

RewriteEngine On
RewriteCond %{REQUEST_METHOD} OPTIONS
#RewriteRule ^(.*)$ $1 [R=200,L,E=HTTP_ORIGIN:%{HTTP:ORIGIN}]
RewriteRule ^(.*)$ blank.php [QSA,L]
</Directory>

```

- the preflight requests can be terminated by nginx like this if used as reverse proxy:

```
location ~ /xapi/xapiproxy.php {
    if ($request_method = 'OPTIONS') {	
			add_header 'Access-Control-Allow-Origin' "$http_origin";
			add_header 'Access-Control-Allow-Credentials' 'true';
			add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS';
			add_header 'Access-Control-Allow-Headers' 'X-Experience-API-Version,Accept,Authorization,Etag,Cache-Control,Content-Type,DNT,If-Modified-Since,Keep-Alive,Origin,User-Agent,X-Mx-ReqToken,X-Requested-With';
			add_header 'Content-Length' '0';
			return 204;
    }
    .......
        
    PHP_FPM_OR_PROXY_CALLS
    .......
}
```

Installation
------------

When you download the Plugin as ZIP file from GitHub, please rename the extracted directory to *XapiCmi5*
(remove the branch suffix, e.g. -master).

1. Copy the XapiCmi5 directory to your ILIAS installation at the followin path
(create subdirectories, if neccessary): Customizing/global/plugins/Services/Repository/RepositoryObject
2. Go to Administration > Plugins
3. Choose action  "Update" for the XapiCmi5 plugin
4. Choose action  "Activate" for the XapiCmi5 plugin

Usage
-----

See [Manual](docs/Manual.pdf) for details.

Version History
===============

* All versions for ILIAS 5.2 and higher are maintained in GitHub: ....

