<div class="taxonomy-lists taxonomy-<?php echo esc_attr($this->taxonomy);?>">
    <div class="row">
        <div class="col col-4">
            <div class="m-portlet">
                <?php
                if( isset($_GET['id']) ) {
                    include_once WOOPANEL_VIEWS_DIR . 'taxonomy-edit.php';	
                }else {
                    include_once WOOPANEL_VIEWS_DIR . 'taxonomy-add.php';	
                }?>
            </div>
        </div>

        <div class="col col-8">
            <?php $this->display();?>
        </div>
    </div>
</div>