<?php

class Template
{
  public static function menu($menu, $page, $status)
  {
    $first = true;
    foreach ($menu as $menuitem) {
      list($pagename, $caption, $passlevel) = $menuitem;
      $show = (($passlevel < 8) || ($status > 8));
      $link = ((($status >= $passlevel)) && ($pagename != $page));
      error_log("$link because $pagename $page $status $passlevel");
      if ($show) {
        if ($first) {
          $first = false;
        } else {
          echo "&nbsp;";
        }
        if ($link) {
          echo '            <a href="' . $pagename . '">';
        }
        echo $caption;
        if ($link) {
          echo "</a>";
        }
        echo "\n";
      }
    }
  }
}
