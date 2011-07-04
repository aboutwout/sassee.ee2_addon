# Sassee

**DISCLAIMER: This add-on is not yet ready for production use and therefor I cannot be held accountable for anything that happens.**

At the moment only _{exp:sassee:file file='site.sass'}_ is fully functional. At runtime it checks if the SASS source file is newer than the parsed CSS file and only runs the parser if that is the case, else it just returns the parsed CSS file.

_{exp:sassee:file template='stylesheets/site'}_ also works, but doesn't have a proper date comparison in place, so it always runs the parser.

**IMPORTANT: Make sure your CSS output folder is writable**

## Tags

    {exp:sassee:file file='site.sass'} // Creates site.css in '/css'
    {exp:sassee:file template='stylsheets/site'} // Creates site.css in '/css'

## Settings

At the moment only the following settings are used

* Path to CSS folder
* URL to CSS folder
* Path to SASS folder

### Still to implement:

* {exp:sassee:output} // Output parsed SASS directly to the template
* Implement all the settings
* Add more parameters to allow settings on a per tag basis.
* SCSS parsing (while I'm at it)