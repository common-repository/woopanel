<?php
/**
 * Layout Footer
 * @package WooPanel/Templates
 * @version 1.1.0
 */
?>
<?php if ( WooPanel_Admin_Options::get_option('dashboard_copyright') ) {?>
<!-- begin::Footer -->
<footer class="m-grid__item m-footer ">
    <div class="m-container m-container--fluid m-container--full-height m-page__container">
        <div class="m-stack m-stack--flex-tablet-and-mobile m-stack--ver m-stack--desktop">
            <div class="m-stack__item m-stack__item--left m-stack__item--middle m-stack__item--last">
                <span class="m-footer__copyright">
                    <?php echo stripslashes( WooPanel_Admin_Options::get_option('dashboard_copyright') );?>
                </span>
            </div>
        </div>
    </div>
</footer>
<!-- end::Footer -->
<?php } ?>