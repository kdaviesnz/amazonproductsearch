<?php

namespace kdaviesnz\amazon;


interface IAmazonCategory
{
    public function get_category_id();
    public function get_category_name();
    public function get_ancestor_categories();
    public function get_number_of_items();
    public function save();

}