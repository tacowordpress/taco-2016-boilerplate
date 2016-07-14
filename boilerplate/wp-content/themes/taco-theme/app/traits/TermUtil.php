<?php
  trait TermUtil {

  /**
   * Get terms used within one post type
   * Covers multiple taxonomies if applicable
   * @return array
   */
    public static function getTermsUsed() {
      global $wpdb;
      $sql_terms = sprintf(
        "SELECT DISTINCT
          t.term_id,
          t.name,
          t.slug,
          tt.taxonomy
        FROM %s AS t
        INNER JOIN %s AS tt ON tt.term_id = t.term_id
        INNER JOIN %s AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN %s AS p ON p.ID = tr.object_id
        WHERE p.post_type = '%s'
        AND p.post_status = 'publish'
        ORDER BY t.name ASC",

        $wpdb->terms,
        $wpdb->term_taxonomy,
        $wpdb->term_relationships,
        $wpdb->posts,
        \Str::machine(get_called_class(), '-')
      );
      return $wpdb->get_results($sql_terms, ARRAY_A);
    }


    public static function getTermsUsedForTaxonomy($taxonomy) {
      $terms = self::getTermsUsed();
      $terms = Collection::groupBy($terms, 'taxonomy');
      return (array_key_exists($taxonomy, $terms))
        ? $terms[$taxonomy]
        : null;
    }
    
  }