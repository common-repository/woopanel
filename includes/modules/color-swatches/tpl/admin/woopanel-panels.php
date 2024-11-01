<div class="woocommerce_attribute m-accordion__item" data-taxonomy="<?php echo $key_tax;?>">
    <div class="m-accordion__item-head" role="tab" id="m_accordion_<?php echo md5($key_tax);?>_head" data-toggle="collapse" href="#m_accordion_6f6cb72d544962fa333e2e34ce64f7191_body" aria-expanded="false">
		<span class="m-accordion__item-title"><?php echo $attribute_label;?></span>
		<span class="m-accordion__item-mode" style="cursor: pointer;"></span>
    </div>
    <div class="m-accordion__item-body" id="m_accordion<?php echo md5($key_tax);?>_body" role="tabpanel" aria-labelledby="m_accordion_<?php echo md5($key_tax);?>_head" data-parent="#m_accordion_2" style="display: none;">
		<div class="m-accordion__item-content">
            <table class="global-table" cellpadding="0" cellspacing="0">
                <tbody>
                    <tr class="first-row">
                        <td>
                            <label style="display: block;">Type</label>
                            <input type="hidden" name="color_swatches_attribute[]" value="<?php echo $key_tax;?>" />
                            <select data-id="<?php echo $key_tax;?>" name="color_swatches[<?php echo $key_tax;?>][type]" class="form-control m-input cs-type-tax">
                                <option value="">(select a type)</option>
                                <?php
                                foreach (NBT_Solutions_Color_Swatches::$types as $k_types => $value) {
                                    $selected = '';
                                    if($wc_attribute_tax->attribute_type == $k_types || isset($cs[$key_tax]['type']) && $cs[$key_tax]['type'] == $k_types){
                                        $selected = ' selected';
                                    }
                                    printf('<option value="%s"%s>%s</option>', $k_types, $selected, $value);
                                }?>
                            </select>
                        </td>
                        <td>
                            <label>Style</label>
                            <ul class="list-style">
                            <?php
                            foreach (NBT_Solutions_Color_Swatches::get_style() as $k_style => $style) {
                                $attribute_style = $selected = $checked = '';


                                if( isset($wc_attribute_tax->attribute_id) ) {
                                    $attribute_style = get_option( 'attribute_style_' . $wc_attribute_tax->attribute_id );
                                }
                                
                                if( isset($cs[$key_tax]['style']) ) {
                                    $attribute_style = $cs[$key_tax]['style'];
                                }

                                if( $attribute_style == $k_style){
                                    $selected = ' selected';
                                    $checked = ' checked';
                                }?>

                                <li class="<?php echo $k_style;?><?php echo $selected;?>">
                                    <input type="radio" class="input-radio" name="color_swatches[<?php echo $key_tax;?>][style]" id="<?php echo $key_tax;?>_<?php echo $k_style;?>" value="<?php echo $k_style;?>"<?php echo $checked;?>>
                                    <div class="cs-radio">
                                        <span></span>
                                    </div>
                                </li>
                                <?php
                            }?>
                            </ul>
                        </td>
                    </tr>

                    <?php if( ! in_array($attribute_type, $exclude_type) ) {?>
                        <tr>
                            <td colspan="2">
                                <table class="<?php echo empty($attribute_type) ? "no-selected " : "default ";?>pm_repeater<?php echo $selected;?>">
                                    <thead>
                                        <tr>
                                            <th class="pm-row-zero" style="width: 5%"></th>
                                            <th class="pm-th" style="width: 50%">Value</th>
                                            <th class="pm-th" style="width: 45%">Display</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach( $terms as $k => $term) {
                                        $value = get_term_meta( $term->term_id, $attribute_type, true );
                                        if( isset($cs[$key_tax]['repeater'][$term->slug]) ) {
                                            $value = $cs[$key_tax]['repeater'][$term->slug];
                                        }
                                        ?>
                                        <tr class="pm-row">
                                            <td class="pm-row-zero order">
                                                <span><?php echo ($k+1);?></span>
                                            </td>
                                            <td class="pm-field">
                                                <div class="pm-input">
                                                    <div class="pm-input-wrap">
                                                        <select class="form-control m-input pm-attributes-field" name="alt_css" data-option="0">
                                                            <option value="<?php echo $term->name;?>"><?php echo $term->name;?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="pm-field">
                                                <div class="pm-input">
                                                    <div class="pm-input-wrap">
                                                        <?php NBT_Color_Swatches_Admin::show_field($attribute_type, 'color_swatches['. $key_tax .'][repeater]['. $term->slug .']', $value);?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php }?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php }else{?>
                        <tr>
                            <td colspan="2">
                                <table class="pm_repeater" style="display: none"></table>
                            </td>
                        </tr>
                    <?php }?>
                </tbody>
            </table>
        </div>
    </div>
</div>
