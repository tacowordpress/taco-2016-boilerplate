<?php
namespace TacoApp;

Class CustomLoader extends \AddBySearch\Loader {

  public static function init()
  {
      add_action('admin_head', '\AddBySearch\AddBySearch::init');
      add_action('admin_footer', '\AddBySearch\AddBySearch::loadClientSide');
      $front_end_loader = new \FrontendLoader\FrontendLoader(
        __DIR__.'/vendor/tacowordpress/addbysearch/src/',
        'addons/addbysearch'
      );
      add_filter('parse_query', function($query) use ($front_end_loader) {
        $front_end_loader->fileServe($query);
        return $query;
      });
      return true;
  }
}
