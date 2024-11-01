<?php
class WooPanel_Category_Checkbox_List_Tree extends Walker_Category {
  /**
   * @see Walker_Category::start_el
   */
  public function start_el( &$output, $category, $depth = 0, $args = array(), $current_object_id = 0 ) {

    extract($args);
    $cat_name = esc_attr( $category->name );
    $cat_name = apply_filters( 'list_terms', $cat_name, $category );

    if ( 'list' == $args['style'] ) {
      $output .= "\t<li";
      $class = "term-item term-item-". $category->term_id;
      $id = $taxonomy ."-". $category->term_id;
      $checked = in_array($category->term_id, $checked) ? "checked" : "";

      if ( !empty($current_category) ) {
        $_current_category = get_term( $current_category, $category->taxonomy );

        if ( $category->term_id == $current_category )
          $class .=  " current-term";
        elseif ( $category->term_id == $_current_category->parent )
          $class .=  " current-term-parent";
      }

      if ( count( get_term_children( $category->term_id, $category->taxonomy ) ) === 0 ) {
        $class .=  " term-item-has-children";
      }
      
      $output .=  " id='". $id ."' class='" . $class . "'";
      $output .= "><label class='m-checkbox m-checkbox--solid m-checkbox--brand'><input type='checkbox' name='".$form_name."[]' id='in-". $id ."' value='$category->term_id' $checked />&nbsp;$cat_name<span></span></label>";
    } else {
      $output .= "\t$cat_name\n";
    }
  }
}