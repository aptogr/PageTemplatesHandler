<?php

/**
 * Add this code only if you are using 
 * the wpbp plugin boilerplate
 */
$pageTemplates = new PageTemplatesHandler();
$this->loader->add_filter( $pageTemplates->page_filter, $pageTemplates, 'add_new_template' );
$this->loader->add_filter( 'wp_insert_post_data', $pageTemplates, 'register_project_templates' );
$this->loader->add_filter( 'template_include', $pageTemplates, 'view_project_template' );

?>