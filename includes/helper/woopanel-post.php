<?php

function woopanel_taxonomies_checkboxtree_metabox($post_id, $taxonomy, $form_name) {
    if(empty($taxonomy)) return;
    $checked = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'ids'));
    $_taxonomy = get_taxonomy( $taxonomy );
    ?>
    <div id="<?php echo 'taxonomy'. $taxonomy;?>" class="categorydiv">
        <div class="list-panel">
            <?php $args = array(
                'walker'     => new WooPanel_Category_Checkbox_List_Tree(),
                'taxonomy'   => $taxonomy,
                'form_name'  => $form_name ? $form_name : "post_{$taxonomy}",
                'title_li'   => '',
                'hide_empty' => false,
                'checked'    => $checked,
            );
            echo '<ul id="'.$args['taxonomy'].'-checklist" class="categorychecklist form-no-clear m-checkbox-list">';
            wp_list_categories($args);
            echo '</ul>'; ?>
        </div>

        <?php if ( current_user_can( $_taxonomy->cap->edit_terms ) ) {?>
        <div id="category-adder">
            <a id="category-add-toggle" href="#category-add"><?php printf( __( '+ %s' ), $_taxonomy->labels->add_new_item );;?></a>
            <p id="link-category-add" class="wp-hidden-child">
                <input type="hidden" name="taxonomy" class="taxonomy_type" value="<?php echo $taxonomy;?>" />
                <input type="text" class="form-control m-input add_category_name" name="add_category_name" placeholder="<?php esc_attr_e( 'New category name' ); ?>">
                <?php
					$parent_dropdown_args = array(
						'taxonomy'         => $taxonomy,
						'hide_empty'       => 0,
						'name'             => 'new' . $taxonomy . '_parent',
						'orderby'          => 'name',
                        'hierarchical'     => 1,
                        'class'             => 'form-control m-input',
						'show_option_none' => '&mdash; ' . $_taxonomy->labels->parent_item . ' &mdash;',
					);

					/**
					 * Filters the arguments for the taxonomy parent dropdown on the Post Edit page.
					 *
					 * @since 4.4.0
					 *
					 * @param array $parent_dropdown_args {
					 *     Optional. Array of arguments to generate parent dropdown.
					 *
					 *     @type string   $taxonomy         Name of the taxonomy to retrieve.
					 *     @type bool     $hide_if_empty    True to skip generating markup if no
					 *                                      categories are found. Default 0.
					 *     @type string   $name             Value for the 'name' attribute
					 *                                      of the select element.
					 *                                      Default "new{$tax_name}_parent".
					 *     @type string   $orderby          Which column to use for ordering
					 *                                      terms. Default 'name'.
					 *     @type bool|int $hierarchical     Whether to traverse the taxonomy
					 *                                      hierarchy. Default 1.
					 *     @type string   $show_option_none Text to display for the "none" option.
					 *                                      Default "&mdash; {$parent} &mdash;",
					 *                                      where `$parent` is 'parent_item'
					 *                                      taxonomy label.
					 * }
					 */
					$parent_dropdown_args = apply_filters( 'post_edit_category_parent_dropdown_args', $parent_dropdown_args );

                    wp_dropdown_categories( $parent_dropdown_args );
                ?>
                <button type="button" id="link-category-add-submit" class="btn btn-primary m-btn m-loader--light m-loader--right" data-security="<?php echo wp_create_nonce( 'category-add-tax' );?>"><?php echo esc_attr( $_taxonomy->labels->add_new_item ); ?></button>
            </p>
        </div>
        <?php }?>
    </div>
    <?php
}

function woopanel_taxonomies_tags_metabox($post_id, $taxonomy, $form_name) {
    if( empty($taxonomy) ) return;
    $checked = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'names'));
    $tax_name = esc_attr( $taxonomy );
    $taxonomy = get_taxonomy( $taxonomy );
    $user_can_assign_terms = current_user_can( $taxonomy->cap->assign_terms );
    $comma = _x( ',', 'tag delimiter' );
    $terms_to_edit = implode(",", $checked); ?>
    <div class="tagsdiv tax_tags" id="<?php echo $tax_name; ?>-wrapper" data-id="<?php echo $tax_name; ?>">
        <div class="jaxtag">
            <div class="nojs-tags hide-if-js">
                <label for="tax-input-<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_or_remove_items; ?></label>
                <p><textarea name="<?php echo $form_name ? $form_name : "tax_input[$tax_name]"; ?>" rows="3" cols="20" class="form-control m-input the-tags" id="<?php echo $form_name ? $form_name : "tax-input-$tax_name"; ?>" <?php disabled( ! $user_can_assign_terms ); ?> aria-describedby="new-tag-<?php echo $tax_name; ?>-desc"><?php echo str_replace( ',', $comma . '', $terms_to_edit ); // textarea_escaped by esc_attr() ?></textarea></p>
            </div>
            <?php if ( $user_can_assign_terms ) : ?>
                <div class="ajaxtag hide-if-no-js">
                    <label class="screen-reader-text" for="new-tag-<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_new_item; ?></label>

                    <div class="input-group m-input-icon m-input-icon--left">
                        <input type="text" class="form-control m-input newtag" data-wp-taxonomy="<?php echo $tax_name; ?>" size="16" autocomplete="off" aria-describedby="new-tag-<?php echo $tax_name; ?>-desc" value="">
                        <span class="m-input-icon__icon m-input-icon__icon--left">
							<span><i class="la la-tags"></i></span>
						</span>
                        <div class="input-group-append">
                            <button class="btn btn-primary m-btn m-btn--icon tagadd" type="button">
								<span>
									<i class="la la-plus"></i>
									<span><?php esc_attr_e('Add'); ?></span>
								</span>
                            </button>
                        </div>
                    </div>
                </div>
                <span class="m-form__help"><?php echo $taxonomy->labels->separate_items_with_commas; ?></span>
            <?php elseif ( empty( $terms_to_edit ) ): ?>
                <p><?php echo $taxonomy->labels->no_terms; ?></p>
            <?php endif; ?>
        </div>
        <ul class="tagchecklist" role="list"></ul>
    </div>

    <div id="most-used-tags">
        <button type="button" id="most-used-tags_link" data-taxonomy="<?php echo $tax_name;?>" data-security="<?php echo wp_create_nonce( 'most-used-tags' );?>"><?php _e( 'Choose from the most used tags' );?></button>
        <div id="tagcloud-post_tag" class="the-tagcloud m-loader--brand m-loader--left"></div>
    </div>
    <?php /* if ( $user_can_assign_terms ) : ?>
	<p class="hide-if-no-js"><button type="button" class="button-link tagcloud-link" id="link-<?php echo $tax_name; ?>" aria-expanded="false"><?php echo $taxonomy->labels->choose_from_most_used; ?></button></p>
	<?php endif; */
}


function woopanel_post_edit_url( $id = 0, $context = 'display' ) {
    if ( ! $post = get_post( $id ) ) return;
    global $woopanel_post_types;

    $post_type_object = get_post_type_object( $post->post_type );
    if ( !$post_type_object ) return;

    $endpoint = $woopanel_post_types[$post->post_type]['slug'];

    if ( !current_user_can( 'edit_post', $post->ID ) ) return;

    $link = esc_url( woopanel_dashboard_url( $endpoint ) .'/?id='. $post->ID );

    return apply_filters( 'woopanel_post_edit_url', $link, $post->ID, $context );
}

function woopanel_post_new_url($post_type = 'post') {
    global $woopanel_post_types;

    $link = esc_url( woopanel_dashboard_url($woopanel_post_types[$post_type]['slug']) );

    return $link;
}

function woopanel_post_delete_url( $id = 0, $deprecated = '', $force_delete = false ) {
    if ( ! empty( $deprecated ) )
        _deprecated_argument( __FUNCTION__, '3.0.0' );

    if ( !$post = get_post( $id ) )
        return;

    $post_type_object = get_post_type_object( $post->post_type );
    if ( !$post_type_object )
        return;

    if ( !current_user_can( 'delete_post', $post->ID ) )
        return;

    $action = ( $force_delete || !EMPTY_TRASH_DAYS ) ? 'delete' : 'trash';

    $endpoint = ($post->post_type == 'post') ? 'article' : $post->post_type;

    $delete_link = add_query_arg( 'action', $action, woopanel_dashboard_url( $endpoint ) .'/?id='. $post->ID );

    return apply_filters( 'woopanel_post_delete_url', wp_nonce_url( $delete_link, "$action-post_{$post->ID}" ), $post->ID, $force_delete );
}

function woopanel_redirect_post($post_id = '') {
    if ( isset($_POST['save']) || isset($_POST['publish']) ) {
        $status = get_post_status($post_id);

        if (isset($_POST['publish'])) {
            switch ($status) {
                case 'pending':
                    $message = 8;
                    break;
                case 'future':
                    $message = 9;
                    break;
                default:
                    $message = 6;
            }
        } else {
            $message = 'draft' == $status ? 10 : 1;
        }
    }

    // 	$location = add_query_arg( 'message', $message, get_edit_post_link( $post_id, 'url' ) );
    // } elseif ( isset($_POST['addmeta']) && $_POST['addmeta'] ) {
    // 	$location = add_query_arg( 'message', 2, wp_get_referer() );
    // 	$location = explode('#', $location);
    // 	$location = $location[0] . '#postcustom';
    // } elseif ( isset($_POST['deletemeta']) && $_POST['deletemeta'] ) {
    // 	$location = add_query_arg( 'message', 3, wp_get_referer() );
    // 	$location = explode('#', $location);
    // 	$location = $location[0] . '#postcustom';
    // } else {
    // 	$location = add_query_arg( 'message', 4, get_edit_post_link( $post_id, 'url' ) );
    // }

    woopanel_redirect( woopanel_post_edit_url ($post_id ) );
}

function woopanel_check_post_lock( $post_id ) {
    if ( ! $post = get_post( $post_id ) ) {
        return false;
    }

    if ( ! $lock = get_post_meta( $post->ID, '_edit_lock', true ) ) {
        return false;
    }

    $lock = explode( ':', $lock );
    $time = $lock[0];
    $user = isset( $lock[1] ) ? $lock[1] : get_post_meta( $post->ID, '_edit_last', true );

    if ( ! get_userdata( $user ) ) {
        return false;
    }

    /** This filter is documented in wp-admin/includes/ajax-actions.php */
    $time_window = apply_filters( 'wp_check_post_lock_window', 150 );

    if ( $time && $time > time() - $time_window && $user != get_current_user_id() ) {
        return $user;
    }

    return false;
}