# Live Editor
### Working prototype for a browser-based CSS/LESS/SASS editing and publishing Tool

#### Application info
Live Editor is a tool for editing and managing CSS/SASS/LESS files. It allow designers and developers to edit CSS and see their changes in realtime. Once changes are made a new draft can be saved, or the stylesheet can be published to the live website.

The inspiration for Live Editor came from the CSS editor built into Chris Pedrick's Web Developer toolbar, and the editors built into Firefox and Google Chrome. We used these regularly as part of our toolkit but wanted a way to save and publish our changes from within the editor. We also wanted an editor that was browser independent. Live Editor runs from within a web browser so no installation is necessary.

NOTE: The tool has been developed and tested on PC Firefox only

#### Background
We started development of Live Editor as a way of learning Javascript and CakePHP therefore much of the code needs some work. It is by no means a finished product but the concept is good and we are pleased to say that we have been using it to edit many projects and it has proved to be a great time saver. It would be great to see this prototype version turned into a finished product.

#### Key features
- Code editor (uses Code Mirror library)
- Element inspector with clickable element paths to move code into the editor
- Colour picker
- Adjustable grid system
- Screen size simulator
- Compatible with CSS/LESS/SASS

## Installation
1. Setup a database using the included schema in app/config/schema.sql
2. Rename app/Config/core.php.default to core.php
3. Rename app/Config/database.php.default to database.php and insert your database credentials
5. Go to http://127.0.0.1/live_editor/live_editors
6. You should now see the test site loaded
7. Debug mode is enabled by default. To turn it off set Configure::write('debug',0) in app/Config/core.php


## Setting up a new job
1. Click the home button
2. Click Job Manager
3. Create a new FTP account if you want to publish your changes to a remote website
4. Click create job
5. Add your job details
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
8. Click the Editor button to go sto the editor
9. Click the home button
10. Click your newly created job to start editing it

## Development notes
Live Editor has been built with Cake PHP 2.5 and jQuery.

## Ideas to improve Live Editor
- Make it work on more browsers and platforms
- Multiple stylesheets per project
- Compass integration to enable separation of code onto different stylesheets
- Allow multiple users to edit the same stylesheet (in a similar way to editing Google Docs)
- Image library and simple image editor
- Pixels to ems units conversion tables

