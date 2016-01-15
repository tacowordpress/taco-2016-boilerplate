<?php

include getenv('HTTP_BOOTSTRAP_WP'); // bootstrap WordPress
include __DIR__.'/loader.php';


if(ENVIRONMENT != 'dev') exit;

$n = $_GET['number'];
$post_type = $_GET['post_type'];


$taxonomies = $post_type::getTaxonomies();

if(\AppLibrary\Arr::iterable($taxonomies)) {
  $taxonomies_terms = Taquito::arrayManipulate(function($k, $tax_name) {
    $terms = get_terms($tax_name, array('hide_empty' => false));
    if(\AppLibrary\Arr::iterable($terms)) {
      return array($tax_name => \AppLibrary\Collection::pluck($terms, 'term_id'));
    }
    return array($k, $tax_name);
  }, $taxonomies);
}

$helper = new $post_type;
$fields = $helper->getFields();
unset($helper);

for($i = 0; $i < $n; $i++) {
  $faker = Faker\Factory::create();

  $faker->addProvider(new Faker\Provider\Resource($faker));

  $instance = new $post_type;
 
  if(\AppLibrary\Arr::iterable($taxonomies)) {
    foreach($taxonomies_terms as $tax_name => $terms) {
      if(preg_match('/type/', $tax_name)) {
        $amount = 1;
      } else {
        $amount = rand(1, count($terms));
      }
      $terms_rand_keys = (array) array_rand($terms, rand(1, $amount));
      $terms_rand_values = array_map(function($i) use ($terms) {
        return $terms[$i];
      }, $terms_rand_keys);
      
      $instance->setTerms(
        $terms_rand_values,
        $tax_name
      );
    }
  }

  $bool_for_checkbox = array(null, '1');

  foreach($fields as $k => $v) {
  
    if(!array_key_exists('type', $v)) continue;
    
    if($v['type'] == 'select') {
      $instance->set($k, array_rand($v['options']));
      continue;
    }
    
    if($v['type'] == 'textarea') {
      $v['type'] = 'text';
    }

    if($v['type'] == 'file') {
      $instance->set($k, $faker->file(
        __DIR__.'/files-images-src',
        __DIR__.'/files-images-dest')
      );
      continue;
    }

    if($v['type'] == 'image') {
      $instance->set($k, $faker->imageUrl(300, 300));
      continue;
    }

    if($v['type'] == 'checkbox' && $k !== 'created_for_testing') {
      $instance->set($k, $bool_for_checkbox[array_rand($bool_for_checkbox)]);
      continue;
    }

    // set specific fields here
    switch($k) {
      case 'created_for_testing':
        $instance->set($k, '1');
      continue;

      default:
      if($v['type'] == 'hidden') continue;
      $instance->set($k, $faker->$v['type']);
    }
  }

  if(preg_match('/resource|research/', $post_type)) {
    $title = $faker->resourceTitle;
  }
  $instance->set('post_title', $title);
  $id = $instance->save();
}