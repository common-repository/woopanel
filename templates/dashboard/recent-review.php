<!--begin:: Widgets/Best Sellers-->
<div class="m-portlet m-portlet--full-height dashboard-recent-reviews">
	<div class="m-portlet__head">
		<div class="m-portlet__head-caption">
			<div class="m-portlet__head-title">
				<h3 class="m-portlet__head-text">
					<?php _e( 'Recent Reviews', 'woopanel' );?>
				</h3>
			</div>
		</div>
	</div>
	<div class="m-portlet__body">

		<!--begin::Content-->
		<div class="tab-content">
			<div class="tab-pane active" id="m_widget5_tab1_content" aria-expanded="true">

                <div class="m-widget3">
                    <?php
                    if( $recent_reviews ) {
                    foreach ($recent_reviews as $key => $comment) {
                    $rating = intval( get_comment_meta( $comment['comment_ID'], 'rating', true ) );
                    $rating_text = sprintf( __( '%s out of 5', 'woocommerce' ), $rating );
                    $avatar_url = get_avatar_url( $comment['comment_author_email'], array(50) ); ?>
                    <div class="m-widget3__item">
                        <div class="m-widget3__header">
                            <div class="m-widget3__user-img">
                                <img class="m-widget3__img" src="<?php echo $avatar_url;?>" alt="<?php echo $comment['comment_author'];?>">
                            </div>
                            <div class="m-widget3__info">
                                <a href="<?php echo get_permalink( $comment['comment_post_ID'] );?>" title="<?php echo $comment['post_title'];?>">
                                    <?php echo $comment['post_title'];?>
                                </a>
                                <?php printf( __( 'reviewed by %s', 'woocommerce' ), '<span class="m-widget3__username">'. $comment['comment_author'] .'</span>' ) ;?><br>
                                <span class="m-widget3__time">
                                    <?php echo date_i18n( get_option( 'date_format' ), strtotime($comment['comment_date']) ) .' @ '. date_i18n( get_option( 'time_format' ), strtotime($comment['comment_date']) ); ?>
                                </span>
                            </div>
                            <span class="m-widget3__status m--font-info" title="<?php echo $rating_text; ?>">
                                <?php
                                echo '<div class="star-rating">';
                                for ( $star = 1; $star <= 5; $star ++ ) {
                                    if( $star <= $rating ) {
                                        echo '<i class="la la-star" style="display: inline"></i>';
                                    }else {
                                        echo '<i class="la la-star-o" style="display: inline"></i>';
                                    }
                                }
                                echo '</div>';
                                ?>
                            </span>
                        </div>
                        <div class="m-widget3__body">
                            <p class="m-widget3__text">
                                <?php echo wp_trim_words($comment['comment_content'], 47, '...');?>
                            </p>
                        </div>
                    </div>
                        <?php
                    }
                    }else {?>
                        <div class="dashboard-block-empty">
                            <i class="flaticon-customer"></i>
                            <h3><?php _e('Your Reviews List Is Empty', 'woopanel');?></h3>
                            <p><?php _e( 'There are no product reviews yet.', 'woocommerce' ); ?></p>
                        </div>
                    <?php }?>
                </div>
			</div>
		</div>

		<!--end::Content-->
	</div>
</div>

