<?php

if ( ! function_exists('css_filename'))
{
  function css_filename($file='')
  {
    if (strstr($file, '/'))
    {
      $file = substr(strrchr($file, '/'), 1).'.css';
    }
    
    return str_replace(array('.sass', '.scss'), '.css', $file);
  }  
}

if ( ! function_exists('get_sass_syntax'))
{
  function get_sass_syntax($file='')
  {
    if ( ! $file) return FALSE;
    
    if (stristr($file, '.scss')) return 'scss';
    
    if (stristr($file, '.sass')) return 'sass';
    
    return FALSE;

  }
}