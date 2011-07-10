# Sassee

At runtime Sassee checks if the SASS source file is newer than the parsed CSS file and only runs the parser if that is the case, else it just returns the parsed CSS file. If you specify an output folder that doesn't exists, Sassee will attempt to create it.

**IMPORTANT: Make sure your CSS output folder is writable and your SASS source folder is readable.**

## Tags

###{exp:sassee:file}###

The file tag outputs the URL to the parsed CSS file. If you haven't set a specific syntax to use, it will try to determine it by looking at the source file extension. Else the extension setting is used for syntax.

Either set the _file_ parameter or the _template_ parameter. If both are set the file parameter is used.

    {exp:sassee:file 
      file='site.sass'
      template='stylesheets/site' 
      [syntax='scss'] // sass|scss
      [style='compressed'] // nested|expanded|compact|compressed
      [sass_path=''] 
      [css_path=''] 
      [css_url=''] 
    }

## Settings

* Path to SASS folder: Server path to the source folder. [default=DOC_ROOT.'/sass/']
* Path to CSS folder: Server path to the output folder. [default=DOC_ROOT.'/css/']
* URL to CSS folder: URL to the output folder. [default='/css']
* Syntax: Default syntax to use. [scss|sass]
* Style: Default style to use. [nested|expanded|compact|compressed]