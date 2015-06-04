# Live Editor
### Working prototype for a browser-based CSS/LESS/SASS editing and publishing Tool

#### Background
We started development of Live Editor as a way of learning Javascript and CakePHP therefore much of the code needs some work. It is by no means a finished product but the concept is good and we are pleased to say that we have been using it to edit many projects and it has proved to be a great time saver. It would be great to see this prototype version turned into a finished product.

NOTE: The tool has been developed and tested on PC Firefox only


#### Application info
Live Editor is a tool for editing and managing CSS/SASS/LESS files. It allow designers and developers to edit CSS and see their changes in realtime. Once changes are made a new draft can be saved, or the stylesheet can be published to the live website.

The inspiration for Live Editor came from the CSS editor built into Chris Pedrick's Web Developer toolbar, and the editors built into Firefox and Google Chrome. We used these regularly as part of our toolkit but wanted a way to save and publish our changes from within the editor. We also wanted an editor that was browser independent. Live Editor runs from within a web browser so no installation is necessary.

#### Key features
- Code editor (uses Code Mirror library)
- Element inspector with clickable element paths to move code into the editor
- Colour picker
- Adjustable grid system
- Screen size simulator
- Compatible with CSS/LESS/SASS



#### IMPORTANT
Use this tool at your own risk!! Due to it being a prototype there are numerous issues which we have not had time to address yet, it is therefore advisable to use this tool with caution.


#### Ideas to improve Live Editor
- Multiple stylesheets per project
- Compass integration to enable separation of code onto different stylesheets
- Allow multiple users to edit the same stylesheet (in a similar way to editing Google Docs)
- Image library and simple image editor
- Pixels to ems units conversion tables


## Installation

The editor loads the website in an iframe. To make changes to it you need to add an entry to your apache vhosts config file.
C:\wamp\bin\apache\apachex.x.x\conf\extra\httpd-vhosts.conf. Here is an example

```
<VirtualHost *:80> 
    ServerAdmin test@localhost
    ServerName localhost
    ServerAlias 127.0.0.1
    
    ProxyPass /test.com/ http://test.com/
    ProxyPassReverse /test.com/ http://test.com/
</VirtualHost>
```

#### Database
Setup database using the included schema app/config/schema.sql

#### App Installation
Download and extract to your local server

#### Test website
To help you try Live Editor, the app comes with a test website which should be installed to www/live_editor_test/

#### Setup a new project
- Click home
- Click Job Manager
- Create a new FTP account first
- Click create job
- Choose your FTP account
