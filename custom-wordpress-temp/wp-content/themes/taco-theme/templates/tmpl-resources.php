<?php /* Template Name: Resources Library */ ?>
<?php get_header(); ?>


<?php $resources_search = new TacoSearch(
  array(
    'conf_name' => 'Resource Library Configuration',
    'column_classes' => 'small-12 medium-8 medium-centered columns',
    'use_text_search_field' => true,
    'taxonomies' => array('resource-type', 'resource-topic'),
    'post_type' => 'resource',
    'lock' => true // stops saving the configuration to the db (set true for prod)
  )
); ?>


<?php echo $resources_search->renderSearchFormHeader(); ?>
<div class="row">
  <div class="small-12 columns">
    <?php echo $resources_search->renderTextSearch(); ?>
  </div>
</div>
<div class="row" class="filters">
  <div class="small-12 medium-6 columns">
    <?php echo $resources_search->renderGroup('taxonomy', 0); ?>
  </div>
  <div class="small-12 medium-6 columns">
    <?php echo $resources_search->renderGroup('taxonomy', 1); ?>
  </div>
</div>
<?php echo $resources_search->renderSearchFormFooter(); ?>


<?php $results = $resources_search->getSearchresults(); ?>
<?php if(\AppLibrary\Arr::iterable($results)): ?>
  <?php $result_posts = \Taco\Post\Factory::createMultiple($results); ?>
  <?php foreach($result_posts as $r): ?>
    <div class="row">
      <div class="small-12 columns">
        <hr>
        <?php echo $r->getTheTitle(); ?>
        <?php echo $r->getTheContent(); ?>

        <?php $terms = $r->getTerms('resource-type'); ?>
        <?php if(Arr::iterable($terms)) : ?>
          <br>
          <br>
          <strong>Resource Type</strong><br>
          <ul>
            <?php foreach($terms as $t): ?>
              <li><?php echo $t->slug; ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php $terms = $r->getTerms('resource-topic'); ?>
        <?php if(Arr::iterable($terms)) : ?>
          <strong>Resource Topic</strong><br>
          <ul>
            <?php foreach($terms as $t): ?>
              <li><?php echo $t->slug; ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <hr>
      </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?php get_footer(); ?>