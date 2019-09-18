<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 18/9/2019
 * Time: 8:59 πμ
 */

class PageTemplatesHandler{


    /**
     * Folder with the page templates
     */
    const TEMPLATES_FOLDER  = 'public/templates/';


    /**
     * The array of templates that this plugin tracks.
     * Set the array key as the page template file and
     * set as value value the name of the template
     */
    const PAGE_TEMPLATES    = array(
        'page-congrapps.php' => 'Congrapps Template',
    );


    /**
     * The array of templates that this plugin tracks.
     */
    protected $templates;


    /**
     * The templates files path
     */
    protected $templates_path;


    /**
     * The add page template filter
     */
    public $page_filter;



    /**
     * Initializes the plugin by setting filters and administration functions.
     */
    public function __construct() {

        $this->templates_path   = plugin_dir_path( dirname( __FILE__ ) ) . self::TEMPLATES_FOLDER;
        $this->templates        = self::PAGE_TEMPLATES;
        $this->page_filter      = $this->get_add_page_template_filter_name();

    }


    private function get_add_page_template_filter_name(){

        return ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.7', '<' ) ? 'page_attributes_dropdown_pages_args' : 'theme_page_templates' );

    }


    /**
     * Register hooks to add the custom page templates
     */
    public function register_hooks(){

        // Add a filter to the attributes meta box to inject template into the cache.
        add_filter( $this->page_filter , array( $this, 'register_project_templates' ) );

        // Add a filter to the save post to inject out template into the page cache
        add_filter(  'wp_insert_post_data', array( $this, 'register_project_templates' ) );

        // Add a filter to the template include to determine if the page has our
        // template assigned and return it's path
        add_filter( 'template_include', array( $this, 'view_project_template') );

    }


    /**
     * Adds our template to the page dropdown for v4.7+
     * @param $posts_templates array
     * @return array
     */
    public function add_new_template( $posts_templates ) {

        return array_merge( $posts_templates, $this->templates );

    }


    /**
     * Adds our template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     * @param $atts array
     * @return mixed
     */
    public function register_project_templates( $atts ) {

        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

        // Retrieve the cache list.
        // If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if ( empty( $templates ) ) {
            $templates = array();
        }

        // New cache, therefore remove the old one
        wp_cache_delete( $cache_key , 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge( $templates, $this->templates );

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add( $cache_key, $templates, 'themes', 1800 );

        return $atts;

    }


    /**
     * Checks if the template is assigned to the page
     * @param $template string
     * @return string
     */
    public function view_project_template( $template ) {

        // Get global post
        global $post;

        // Return template if post is empty
        if ( ! $post ) {
            return $template;
        }

        // Return default template if we don't have a custom one defined
        if ( ! isset( $this->templates[ get_post_meta( $post->ID, '_wp_page_template', true ) ] ) ) {
            return $template;
        }

        $file = $this->templates_path. get_post_meta( $post->ID, '_wp_page_template', true  );

        // Just to be safe, we check if the file exist first
        if ( file_exists( $file ) ) {
            return $file;
        } else {
            echo $file;
        }

        // Return template
        return $template;

    }

}
