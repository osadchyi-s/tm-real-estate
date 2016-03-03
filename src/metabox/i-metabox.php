<?php

interface I_Metabox {

    /**
     * Build a metabox instance.
     *
     * @param string $title The metabox title
     * @param string $postType The post type name where the metabox is displayed
     * @param array $options Metabox parameters (id, context, priority,...)
     * @param IRenderable $view A custom view used by the metabox to render
     * @return \Themosis\Metabox\IMetabox
     */
    public static function make($title, $postType, array $options = array());

    /**
     * Register the metabox to WordPress
     *
     * @param array $fields A list of custom fields to assign.
     * @return \Themosis\Metabox\IMetabox
     */
    public function set(array $fields = array());

    /**
     * Define a custom capability to check before adding the metabox.
     *
     * @param string $capability
     * @return \Themosis\Metabox\IMetabox
     */
    public function can($capability);

    /**
     * Define a list of validation rules to execute on metabox fields.
     *
     * @param array $rules
     * @return \Themosis\Metabox\IMetabox
     */
    public function validate(array $rules = array());

    /**
     * Pass custom data to the metabox view.
     *
     * @param string $key
     * @param mixed $value
     * @return \Themosis\Metabox\IMetabox
     */
    public function with($key, $value = null);

}