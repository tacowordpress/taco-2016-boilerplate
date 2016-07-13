<?php

/**
 * This script uses the Gather Content API to fetch pages
 * The method "savePagesToWP" will save these pages retaining
 *  parent child relationships.
 * If the second param of "savePagesToWP" is set to true, it will also
 *  try and save data to the post_content field.
 * @link https://gathercontent.com/support/developer-api/
 */

include getenv('HTTP_BOOTSTRAP_WP');
if(ENVIRONMENT === 'prod') exit;

include __DIR__.'/TacoGatherContent.php';


$gather = new TacoGatherContent(
  // url e.g. "https://yourwebsite.com.gathercontent.com/api/0.4/"
  'https://vermilion.com.gathercontent.com/api/0.4/',
  // api key e.g. "29dsaasdkfja320923rkasdjfa0932kjadfkl2182asdjasdfkasdjkf"
  '',
  // project id e.g. "72593"
  null
);
$pages = $gather->getPages();

/*
 * savePagesToWP()
 * param: $pages | array | collection of pages
 * param: $save_post_content | boolean | tries to find a
 *  field in gather content where the label contains
 *  the word body or content
*/
$gather->savePagesToWP($pages, true);