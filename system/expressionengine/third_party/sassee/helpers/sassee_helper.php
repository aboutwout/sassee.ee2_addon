<?php

if ( ! function_exists('css_filename'))
{
  function css_filename($file='')
  {
    if (strstr($file, '/'))
    {
      $file = substr(strrchr($file, '/'), 1).'.css';
    }
    
    return str_replace('.sass', '.css', $file);
  }  
}
