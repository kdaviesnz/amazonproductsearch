<?php



namespace kdaviesnz\amazon;



class AmazonCategory implements IAmazonCategory
{
    private $category_id = '';
    private $category_name = '';
    private $ancestor_categories = array();
    private $number_of_items = 0;

    /**
     * AmazonCategory constructor.
     * @param string $category_id
     * @param string $category_name
     * @param array $ancestor_categories
     */
    public function __construct( $category_id, $category_name, $ancestor_categories, $number_of_items ) {
        $this->category_id = $category_id;
        $this->category_name = $category_name;
        $this->ancestor_categories = $ancestor_categories;
        $this->number_of_items = $number_of_items;
    }

    /**
     * @return string
     */
    public function get_category_id()
    {
        return $this->category_id;
    }

    /**
     * @return string
     */
    public function get_category_name()
    {
        return $this->category_name;
    }

    /**
     * @return array
     */
    public function get_ancestor_categories()
    {
        return $this->ancestor_categories;
    }

    /**
     * @return int
     */
    public function get_number_of_items()
    {
        return $this->number_of_items;
    }


    // Remove
    public function save() {

        global $wpdb;

        $sql = $wpdb->prepare(
            "INSERT IGNORE INTO `wp_amazon_amazon_categories` (`category_id`, `category_name`) 
              VALUES ('%s', '%s');",
            $this->get_category_id(),
            $this->get_category_name()
        );
        $wpdb->query( $sql );
        $error = $wpdb->last_error;
        if ( ! empty( $error ) ) {
            throw new \Exception( $error . $sql );
        }

        $ancestors = $this->get_ancestor_categories();


        foreach( $ancestors as $ancestor ) {

            $ancestor->save();

            $sql = $wpdb->prepare(
                "INSERT IGNORE INTO `wp_amazon_categories_categories` (`categoryID`, `parentCategoryID`)
            VALUES ('%s', '%s');",
                $this->get_category_id(),
                $ancestor->get_category_id()
            );

            $wpdb->query($sql);
            $error = $wpdb->last_error;
            if ( ! empty( $error ) ) {
                throw new \Exception( $error . $sql);
            }
        }

    }



}