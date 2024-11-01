<?php
/**
 * @package WooPanel/Templates
 * @version 1.1.0
 */
?>

<div class="content-empty <?php echo $class; ?>" <?php echo $style ? "style='{$style}'" : ''; ?>>
    <?php if( !empty($icon) ) echo "<i class='{$icon}'></i>"; ?>
    <?php if( !empty($title) ) echo "<h3>{$title}</h3>"; ?>
    <?php if( !empty($subtitle) ) echo "<p>{$subtitle}</p>"; ?>
</div>