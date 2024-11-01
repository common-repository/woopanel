<?php
class NBCS_Update {
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'require_update_database') );
	}

	public function require_update_database() {
		$lists = $this->get_total_required();
		if( ! empty($lists) && ! get_option('nbcs_update_required') ) {
		?>
		<div id="nbcs-update-error" class="error">
        	<p><strong>Color Swatches Data Update</strong> - We need to update your store's database to the latest version.</p>
        	<input type="button" name="upgrade" id="netbase_upgrade" class="button button-primary regular" value="Update Now">

        	<div class="nbcs-update-lists" style="display: none">
        		<ul>
        			<?php foreach( $lists as $k => $rs) {?>
        			<li id="nbcs<?php echo $k;?>" data-product_id="<?php echo $rs['id'];?>">
        				<a href="<?php echo $rs['link'];?>" target="_blank"><?php echo $rs['title'];?> <strong>(ID: <?php echo $rs['id'];?>)</strong></a>
        				<div class="nbcs-results" style="display: none;">
        					<img src="<?php echo NBT_CS_URL;?>assets/img/loading.gif" class="nbcs-loader">
        					<span class="nbcs-status"></span>
        				</div>
        			</li>
        			<?php }?>
        		</ul>
        	</div>
        </div>
        <style>
        #netbase_upgrade{
		    background: #ff5722;
		    border-color: #e44918 #d74213 #ff5722;
		    box-shadow: 0 1px 0 #c52f00;
		    text-shadow: 0 -1px 1px #ff5722, 1px 0 1px #ff5722, 0 1px 1px #ff5722, -1px 0 1px #ff5722;
		    margin-bottom: 10px;
        }
        .nbcs-update-lists li {
        	padding-bottom: 5px;
        	border-bottom: 1px solid #f0f0f0;
        }

		.nbcs-update-lists li:after {
		  content: "";
		  clear: both;
		  display: table;
		}
		.nbcs-update-lists li:last-child {
			border-bottom: 0;
		}
        .nbcs-update-lists li a {
            float: left;
        	text-decoration: none;
        	color: #444;
		    width: 200px;
		    display: inline-block;
		    white-space: nowrap;
		    overflow: hidden;
		    text-overflow: ellipsis;
        }
        .nbcs-results {
            position: relative;
        	display: inline-block;
    		margin-left: 10px;
    		padding-left: 45px;
        }
        .nbcs-loader {
		    position: absolute;
		    top: -12px;
		    left: 0;
        }
        .nbcs-status {
        	font-weight: 600;
        }
    	</style>
    	<script type="text/javascript">
    		jQuery(document).ready(function($) {
    			$(document).on('click', '#netbase_upgrade', function(e) {
    				e.preventDefault();
    				$('.nbcs-update-lists').show();

    				nbcs_next(0);

    			});

    			function nbcs_next(step) {
    				var $li = $('#nbcs' + step),
                    $product_id = $li.attr('data-product_id');

    				$li.addClass('processed');
    				$li.find('.nbcs-loader').show();
                    $li.find('.nbcs-results').show();
    				$li.find('.nbcs-status').text('Please wait...');

    				$('.nbcs-update-lists li:not(.processed) .nbcs-results').hide();

   					$.ajax({
						url: '<?php echo admin_url('/admin-ajax.php');?>',
						data: {
							action:     'nbcs_update_required',
							step: step,
                            product_id: $product_id
						},
						type: 'POST',
						datatype: 'json',
						success: function( response ) {
							if(response.end != undefined ) {
                                $('#netbase_upgrade').prop('disabled', true);
                                $li.find('.nbcs-loader').hide();
                                $li.find('.nbcs-status').html('<span style="color: green">Successfully!</span>');
								setTimeout(function(){ alert(response.message); }, 500);
								return;
							}

							if( response.complete != undefined ) {
								$li.find('.nbcs-loader').hide();
								$li.find('.nbcs-status').html(response.message);
								if(response.next != undefined ) {
									nbcs_next(response.next);
								}
							}else {
								if(response.not_found != undefined ) {
									alert(response.message);
								}else {
									$li.find('.nbcs-status').html(response.message);
								}
							}
						},
						error:function(){
							alert('There was an error when processing data, please try again !');
							nbtcs_ajax.unblock();
						}
					});
    			}
    		});
    	</script>
        <?php
    	}
	}

	public function get_total_required() {
    	$color_swatches = get_transient('id_color_swatches');
    	if( ! $color_swatches ) {
    		global $wpdb;

    		$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}postmeta AS pmeta LEFT JOIN {$wpdb->prefix}posts AS post ON pmeta.post_id = post.ID WHERE pmeta.meta_key = '_color_swatches' AND pmeta.meta_value = 'on' ORDER BY pmeta.post_id ASC" );

    		if( $results ) {
    			$color_swatches = array();
    			foreach( $results as $rs) {
    				$color_swatches[] = array(
    					'id' => $rs->post_id,
    					'title' => $rs->post_title,
    					'link' => get_permalink($rs->ID)
    				);
    			}
    			
    			set_transient('id_color_swatches', $color_swatches);
    		}
    	}
    	return $color_swatches;
	}

}
new NBCS_Update();