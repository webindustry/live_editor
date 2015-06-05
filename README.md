# Live Editor
### Working prototype for a browser-based CSS/LESS/SASS editing and publishing Tool

#### Application info
Live Editor is a tool for editing and managing CSS/SASS/LESS files. It enables you to edit CSS and see your changes in realtime. Once changes are made a new draft can be saved, or the stylesheet can be published to the live website.

The inspiration for Live Editor came from the CSS editor built into Chris Pedrick's Web Developer toolbar, and the editors built into Firefox and Google Chrome. We used these regularly as part of our toolkit but wanted a way to save and publish our changes from within the editor. We also wanted an editor that was browser independent. Live Editor runs from within a web browser so no installation is necessary.

We started development of Live Editor as a way of learning Javascript and CakePHP therefore much of the code needs some work. It is by no means a finished product but the concept is good and we are pleased to say that we have been using it to edit many projects and it has proved to be a great time saver. It would be great to see this prototype version turned into a finished product.

**_NOTE: The tool has been developed and tested on PC Firefox only_**

Quick video introduction: http://autode.sk/1wXSBPS


#### Key features
- Code editor (uses Code Mirror library)
- Element inspector with clickable element paths to move code into the editor
- Colour picker
- Adjustable grid system
- Screen size simulator
- Compatible with CSS/LESS/SASS

## Installation (quick)
1. Download the app into /www/live_editor
	- e.g. git clone https://github.com/webindustry/live_editor.git c:/wamp/www/live_editor
2. Setup a database using the included schema in config/schema.sql
3. Rename Config/config.php.default to config.php
4. Rename Config/core.php.default to core.php
5. Rename Config/database.php.default to database.php and insert your database credentials
6. Go to http://127.0.0.1/live_editor/live_editors
7. You should now see the test site loaded. The code editor is hidden by default, hover over the semi-transparent bar at the bottom of the screen to see bring up the editor
8. Debug mode is enabled by default. To turn it off set Configure::write('debug',0) in Config/core.php

##Installation (custom)
To install the app in a different location, the following paths will need to be changed:

1. Config/core.php INSTALL_DIR and FULL_BASE_URL_TO_APP
2. FS_ROOT in Config/config.php
3. Tot use the test jobs that come included with the app, you will also need to update their path settings in the job manager


## Setting up a new job
1. Click the home button
2. Click Job Manager
3. Create a new FTP account if you want to publish your changes to a remote website
4. Click create job
5. Add your job details. Example: These would be the settings needed to load http://mysite.com/cms/home and edit http://mysite.com/cms/css/styles.css
  - URL: mysite.com
  - Default URI: cms/home
  - Stylesheet URI Root: http://mysite.com/cms
  - Stylesheet URI:	css/styles.css
6. Choose your FTP account
7. Allow the target website to be loaded via Apache proxy. The editor loads the target website in an iframe so to see changes to a live website you need to add an entry to your Apache httpd-vhosts.conf e.g.
C:\wamp\bin\apache\apachex.x.x\conf\extra\httpd-vhosts.conf. Here is an example:

```
<VirtualHost *:80> 
    ServerAdmin test@localhost
    ServerName localhost
    ServerAlias 127.0.0.1
    
    ProxyPass /test.com/ http://test.com/
    ProxyPassReverse /test.com/ http://test.com/
</VirtualHost>
```
8. Click the Editor button to go to the editor
9. Click the home button
10. Click your newly created job to start editing it

## Development notes
- Live Editor has been built with Cake PHP 2.5 and jQuery.
- The tool has been developed and tested on PC Firefox only

## Ideas to improve Live Editor
- Make it work on more browsers and platforms
- Multiple stylesheets per project
- Compass integration to enable separation of code onto different stylesheets
- Allow multiple users to edit the same stylesheet (in a similar way to editing Google Docs)
- Image library and simple image editor
- Pixels to ems units conversion tables

