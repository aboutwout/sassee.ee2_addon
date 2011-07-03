<?php

if ( ! function_exists('css_filename'))
{
  function css_filename($file='')
  {
    return str_replace('.sass', '.css', $file);
  }  
}
