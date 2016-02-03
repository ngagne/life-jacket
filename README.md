# Life Jacket

The Life Jacket framework allows web designers/developers to quickly and naturally create a website (using standard HTML files) while allowing clients to safely update only the individual pieces of content that they need. Happy clients and happy designers.

## Requirements

  * PHP 5.3+
  
## Installation

Move all files to the directory just above your public web directory. For example, if your website files are loaded from `/var/www/html/example.com/public/`, then the files should go in `/var/www/html/example.com/`. If your publically accessible web root directory is not named "public", then you should change your website's web server entry to point to the "public" directory. If this is not possible, then you can renamed the "public" directory (just make sure you also rename the `publicDir` variable at the top of the `Gruntfile.js` before compiling so Grunt knows where to place the compiled CSS/JS files.

In the root directory, run `bower install` to install Foundation, SwiftMailer, jQuery and it's dependencies to the `/bower_components/` directory. Compile the CSS/JS using the instructions in the "Compiling CSS/JS" section below.
 
You should now be able to access your website's homepage as well as the administrative area `/admin/`. Next, you can start adding your own HTML template files in the `/app/layouts/` and `/app/views/` directories.
  
The following folders should be writable by the server:

  * `/app/config/*` (stores the settings config.ini file as well as the strings.ini file)
  * `/app/tmp/*` (stores cached output when using files as a caching mechanism)
  * `/public/img/uploads/*` (stores images uploaded through the admin interface)
  
## Compiling CSS/JS

### Requirements

  * [NodeJS](https://nodejs.org/en/download/)
  * [Grunt](http://gruntjs.com/installing-grunt)

Run `npm i` in the command prompt to install any missing Node dependencies. Then, run `grunt` to compile the CSS/JS and watch the SASS/JS source files.

## Getting Started

After you have everything running, start by reviewing the example HTML files in the `/app/layouts/` and `/app/views/` directories. This is an effective way to get familiar with the concept of tokens and the relationship they have with Life Jacket's front-end and back-end.
  
## Documentation

### Tokens 
#### Overview

Templates are standard HTML files with the addition of tokens such as this: `[[content]]`. When a template contains a token, an editable field will automatically be made available in the admin area. Tokens can be defined within a template.
 
Tokens that have a single underscore (_) prefix are considered global tokens. Global tokens share a single editable field and it's value is used on all pages of the website. Global tokens can be defined within a template.

Tokens that have a double underscore (__) prefix are considered dynamic tokens. Dynamic tokens are not editable as they represent content that are generated at the page request. Dynamic tokens **cannot** be defined within a template.

Tokens that contain an equals sign (=) are considered property tokens. Property tokens are used to set special values that the application will use when processing a request, such as the title of the page or a template include. 

Tokens helpers have a specific suffix in the format of `[[.../suffix]]` and allow for special handling of tokens in the admin area and when rendered on a page. One example is the image helper `[[.../img]]` which will render the admin are field as a file input with a thumbnail preview and render on the page as a path to the uploaded image. New tokens can be added to the system to expand functionality.   

#### Dynamic Tokens

  * `[[__head]]` render auto-generated `<head>` elements such as the title and meta description
  * `[[__year]]` current 4-character year
  * `[[__recaptcha_sitekey]]` ReCaptcha site key
  * `[[__google_analytics_id]]` Google Analytics site ID
  * `[[__site_name]]` title of the website
  * `[[__errors]]` form validation errors for display
  * `[[__content]]` defines the content area within a layout
  
#### Property Tokens

  * `[[include=...]]` includes a template from the /layouts/_partials/ directory. For example, [[include-nav]] will include the following file: /layouts/_partials/nav.html.
  * `[[title=...]]` defines the page title, overriding any automatically generated one.
  * `[[layout=...]]` defines what layout should be used to wrap this view.
  * `[[meta_desc=...]]` defines the page's meta description
  
#### Special Suffix Tokens
  * `[[.../img]]` creates an image URL which can be edited through the admin area. A unique string should be used before the `|img` suffix just as you would with a standard token.
  * `[[.../e]]` removes any HTML tags before rendering to the page.
  * `[[.../youtube]]` extracts the YouTube video ID from an embed code or url.

#### Custom Token Helpers
New token helpers can be added by creating a new PHP class file in `/vendor/ATC/TokenHelpers/` which extends the `\ATC\TokenHelpers\Text` base class (`/vendor/ATC/TokenHelpers/Text.php`). You can review the `\ATC\TokenHelpers\Img` class to see a good example of how to extend token functionality.
  
### Templates

As a request comes into the application (http://sample.com/about-us) it is routed to the appropriate view. The application will take the path "/about-us" and try to find a specific view in the /app/views/ directory using the filename "about-us.html". If it finds the view file it will then scan the file to find a layout specified (`[[layout=...]]`). If a layout is specified, the system will load that specific layout from the /app/layouts/ directory. Otherwise, it will load the /app/layouts/index.html file. Then, the application will place the contents of the view into the `[[__content]]` section of the layout. Next, any included files (`[[include=...]]`) specified will be gathered from the /app/layouts/_partials/ directory and their contents will be loaded into the combined HTML. The application will then process any dynamic or user-defined tokens and finally output the HTML to the web browser.

#### Partials
Partials (or partial templates) are stored in the `/app/layouts/_partials/` directory. The application will look for files in this directory when processing an include token (`[[include=...]]`).

#### System Partials
System partials are stored in the `/app/layouts/_system/` directory. These partials are not included by templates but are used by the system to render dynamic content, such as error messages. System partials will contain tokens specific to a certain part of the application. You can freely edit these templates, but you should not modify/remove the tokens contained within.

#### Multi-level Paths

If a multi-level path is requested (http://sample.com/about/history) the application will look for an `index.html` file in a subdirectory within `/app/views/` matching the path of the request. If it doesn't find one, it will look for a `.html` file with the same name as the last part of the path, within a subdirectory matching the path of the request, minus the last part.

Here is an example of paths that would match a request to `/about/staff/ceo` starting with the first match:

  * `/views/about/staff/ceo/index.html`
  * `/views/about/staff/ceo.html`
  * `/views/error.html` *(indicates a 404 error)*
  
### Forms
Forms can be generated using standard HTML within the view file and must contain the following field: `<input type="hidden" name="form_submit" value="contact-us">`. The value of this hidden field corresponds to which email template to load from the `/app/layouts/emails/` directory. If it doesn't find a template file with that name, then it will use the `/app/layouts/emails/index.html` file. When creating your own email template, make sure to include the `[[__content]]` token.

#### Validation
The application will process most HTML5 validation attributes (required, pattern, type="email", etc.) 
 
If a validation error occurs, the application will populate a list of error messages into the `[[__errors]]` token. HTML for these validation messages are stored in the `/app/layouts/_system/` directory.

  
### Emails
When a form is submitted, an email will be generated containing a list of the submitted data and sent to the email addresses specified in the `system_mail_to` configuration file setting. Specific email settings are stored in the `/app/config/config.ini` file.

### Configuration
Application settings are stored in the `/app/config/config.ini` file. The config file contains sections that will load depending on the type of environment (production, development, etc.) You can specify the environment by setting the `APPLICATION_ENV` environment variable. If an environment doesn't match, the production environment will be loaded by default. Here is a list of the available settings:

  * **site_name** The name of the website. Used in the `<title>` element as well as the `[[__site_name]]` token.
  * **strings_adapter** The method with which to store token strings. This can be set to `ini`, `apc`, `apcu` or `memcached`.
  * **cache_enabled** If the application should cache HTML output. Set to `true` or `false`
  * **cache_adapter** The method with which to cache HTML. This can be set to `file`, `apc`, `apcu` or `memcached`.
  * **admin_username** The username the administrator logs in with.
  * **admin_password** The password the administrator logs in with.
  * **system_mail_from_name** The sender name that appears in system generated emails.
  * **system_mail_from_email** The sender email address that appears in system generated emails.
  * **system_mail_to** Comma-separated list of email addresses to receive system generated emails.
  * **system_mail_subject** Subject line of system generated emails.
  * **mail_transport** The method with which to send emails. Can be set to `smtp`, `sendmail` or `mail`.
  * **mail_smtp_host** The host address to use if using SMTP to send mail.
  * **mail_smtp_port** The port to use if using SMTP to send mail.
  * **mail_smtp_username** = The username to use if using SMTP to send mail.
  * **mail_smtp_password** = The password to use if using SMTP to send mail.
  * **recaptcha_on** If the application should validate recaptcha on forms.
  * **recaptcha_site_key** The site key provided by recaptcha.
  * **recaptcha_secret_key** = The secret key provided by recaptcha.
  * **google_analytics_id** Google Analytics site ID
  * **site_root** Used for determining where to redirect users during certain events.
  * **image_uploads_path** Determines where images should be stored when uploading through the admin interface.
  
## Administration
The administrative area can be accessed using the /admin URL. The username and password is specified in the `/app/config/config.ini` file. If cache is enabled, it will be reloaded when saving any change in the admin area.