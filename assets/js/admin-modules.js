jQuery(function(s){if("undefined"!=typeof woocommerce_admin_meta_boxes_variations)var r=woocommerce_admin_meta_boxes_variations.ajax_url,n=woocommerce_admin_meta_boxes_variations.load_variations_nonce,l=(nb_pricematrix.input_price_nonce,nb_pricematrix.save_price_nonce,woocommerce_admin_meta_boxes_variations.post_id),a=!0;else{if(0<s('[name="post_title"]').length&&"product"==s("#post_type").val())r=WooPanel.ajaxurl,n=WooPanel.product.load_variations_nonce,WooPanel.product.input_price_nonce,WooPanel.product.save_price_nonce,l=WooPanel.product.post_id;a=null}var t=window.wp,c={init:function(){jQuery().wpColorPicker&&s("#term-color, .term-color").wpColorPicker(),jQuery().spectrum&&this._spectrum(),s(document).on("click",".nbtcs-upload-image-button",this.upload_image),s(document).on("click",".nbtcs-remove-image-button",this.remove_upload_image),s(document).on("click","#_color_swatches",this.enable_color_swatches),s(document).on("click","li.color_swatches_options a, .color_swatches_tab a",this.initial_load),s(document).on("click",".save_color_swatches",this.save_color_swatches),s(document).on("click","#color_swatches .m-accordion__item-head",this.openAccordion),s(document).on("click",".cs-radio",this.style_selected),s(document).on("click",".enable_custom_checkbox",this.custom_repeater),s(document).on("change",".cs-type-tax",this.change_type),s(document).ajaxComplete(this.remove_field_tags),this.check_enable_color_swatches()},openAccordion:function(e){e.preventDefault();var t=s(this).closest(".m-accordion__item");t.hasClass("open")?(t.removeClass("open"),t.find(".m-accordion__item-body").stop().slideUp()):(t.addClass("open"),t.find(".m-accordion__item-body").stop().slideDown())},_spectrum:function(){s("#term-color, .term-color").each(function(e){var a=s(this),t=s(this).val();s(this).spectrum({allowEmpty:!0,color:t,showInput:!0,containerClassName:"full-spectrum",showInitial:!0,showPalette:!0,showSelectionPalette:!0,showAlpha:!0,maxPaletteSize:10,preferredFormat:"hex",move:function(e){var t="transparent";e&&(t=e.toHexString()),a.val(t)},palette:[["rgb(0, 0, 0)","rgb(67, 67, 67)","rgb(102, 102, 102)","rgb(204, 204, 204)","rgb(217, 217, 217)","rgb(255, 255, 255)"],["rgb(152, 0, 0)","rgb(255, 0, 0)","rgb(255, 153, 0)","rgb(255, 255, 0)","rgb(0, 255, 0)","rgb(0, 255, 255)","rgb(74, 134, 232)","rgb(0, 0, 255)","rgb(153, 0, 255)","rgb(255, 0, 255)"],["rgb(230, 184, 175)","rgb(244, 204, 204)","rgb(252, 229, 205)","rgb(255, 242, 204)","rgb(217, 234, 211)","rgb(208, 224, 227)","rgb(201, 218, 248)","rgb(207, 226, 243)","rgb(217, 210, 233)","rgb(234, 209, 220)","rgb(221, 126, 107)","rgb(234, 153, 153)","rgb(249, 203, 156)","rgb(255, 229, 153)","rgb(182, 215, 168)","rgb(162, 196, 201)","rgb(164, 194, 244)","rgb(159, 197, 232)","rgb(180, 167, 214)","rgb(213, 166, 189)","rgb(204, 65, 37)","rgb(224, 102, 102)","rgb(246, 178, 107)","rgb(255, 217, 102)","rgb(147, 196, 125)","rgb(118, 165, 175)","rgb(109, 158, 235)","rgb(111, 168, 220)","rgb(142, 124, 195)","rgb(194, 123, 160)","rgb(166, 28, 0)","rgb(204, 0, 0)","rgb(230, 145, 56)","rgb(241, 194, 50)","rgb(106, 168, 79)","rgb(69, 129, 142)","rgb(60, 120, 216)","rgb(61, 133, 198)","rgb(103, 78, 167)","rgb(166, 77, 121)","rgb(91, 15, 0)","rgb(102, 0, 0)","rgb(120, 63, 4)","rgb(127, 96, 0)","rgb(39, 78, 19)","rgb(12, 52, 61)","rgb(28, 69, 135)","rgb(7, 55, 99)","rgb(32, 18, 77)","rgb(76, 17, 48)"]]})})},initial_load:function(){d.block();var t=s("#tpl-color-swatches").html(),e=s("#variable_product_options").find(".woocommerce_variations").data("attributes");if(null==e)0;else Object.keys(e).length;s.ajax({url:r,data:{action:"cs_load_variations",security:n,product_id:l,attributes:e,is_admin:a},type:"POST",datatype:"json",success:function(e){s(".woocommerce-message").remove(),null!=e.complete?(s("#color_swatches").html(t),s(".color_swatches.wc-metaboxes").html(e.html),jQuery().wpColorPicker&&s(".term-color").wpColorPicker(),jQuery().spectrum&&c._spectrum()):0<s("#m-portlet__tabright #color_swatches").length?s("#m-portlet__tabright #color_swatches").html(s("#msg-js").html()):s("#price_matrix_options_inner").html(s("#msg-js").html()),d.unblock()},error:function(){alert("There was an error when processing data, please try again !"),d.unblock()}})},remove_field_tags:function(e,t,a){if(t&&4===t.readyState&&200===t.status&&a.data&&(0<=a.data.indexOf("_inline_edit")||0<=a.data.indexOf("add-tag"))){var i=wpAjax.parseAjaxResponse(t.responseXML,"ajax-response");if(!i||i.errors)return;s("#term-color, .term-color").wpColorPicker(),s("#wpbody-content").trigger("click"),s(".wp-color-result").css("background-color","")}},style_selected:function(){var e=s(this).closest("li");s(this).closest("ul").find("li").removeClass("selected"),s(this).closest("ul").find(".input-radio").removeAttr("checked"),e.find(".input-radio").attr("checked","checked"),e.addClass("selected")},custom_repeater:function(){var e=s(this).closest(".woocommerce_attribute_data").find(".pm_repeater");s(this).is(":checked")?e.show():e.hide()},change_type:function(){var a=s(this).closest(".woocommerce_attribute"),e=a.attr("data-taxonomy"),i=s(this).closest(".woocommerce_attribute").find(".pm_repeater"),o=a.find(".cs-type-tax").val(),t=s("#variable_product_options").find(".woocommerce_variations").data("attributes");if(null==t)0;else Object.keys(t).length;"0"!=o?(d.block(),s.ajax({url:r,data:{action:"cs_load_style",security:n,product_id:l,tax:e,type:o},type:"POST",datatype:"json",success:function(e){var t=JSON.parse(e);s(".woocommerce-message").remove(),null!=t.complete&&(""==o||"radio"==o||"label"==o?(a.find(".pm_repeater").empty(),a.find(".pm_repeater").hide()):(i.show().html(t.html),jQuery().wpColorPicker&&s(".term-color").wpColorPicker(),jQuery().spectrum&&c._spectrum())),d.unblock()},error:function(){alert("There was an error when processing data, please try again !"),d.unblock()}})):i.hide()},upload_image:function(e){e.preventDefault();var a=s(this).closest(".nbtcs-wrap-image"),i=t.media.frames.downloadable_file=t.media({title:nbtcs.i18n.mediaTitle,button:{text:nbtcs.i18n.mediaButton},multiple:!1});i.on("select",function(){var e=i.state().get("selection").first().toJSON();if(0<a.closest(".pm-row").length){var t=a.closest(".pm-row");t.find("input.nbtcs-term-image").val(e.id),t.find(".nbtcs-remove-image-button").show(),t.find("img").attr("src",e.url)}else a.addClass("class_name"+e.id),a.find("input.nbtcs-term-image").val(e.id),a.find(".nbtcs-remove-image-button").show(),a.find("img").attr("src",e.url)}),i.open()},remove_upload_image:function(){var e=s(this);return e.siblings("input.nbtcs-term-image").val(""),e.siblings(".nbtcs-remove-image-button").show(),e.parent().prev(".nbtcs-term-image-thumbnail").find("img").attr("src",nbtcs.placeholder),!1},check_enable_color_swatches:function(){var e=s("#_color_swatches");e.closest("label").hasClass("yes")?(e.prop("checked",!0),s(".color_swatches_options").removeClass("hide")):(e.prop("checked",!1),s(".color_swatches_options").addClass("hide"))},enable_color_swatches:function(){s(this).is(":checked")?s(".color_swatches_options").removeClass("hide"):(s(".color_swatches_options").addClass("hide"),s("#color_swatches").is(":visible")&&(s(".woocommerce_options_panel").hide(),s("#inventory_product_data").show(),s(".product_data_tabs > li").removeClass("active"),s(".inventory_options.inventory_tab").addClass("active")))},save_color_swatches:function(){d.block();var a=[],i=[];s(".cs-type-tax :selected").each(function(e,t){a[e]=s(t).val(),i[e]=s(t).closest("select").attr("data-id")});var n=[],c=[];s("#color_swatches .woocommerce_attribute").each(function(e,t){n[e]=s(t).find(".input-radio:checked").attr("value");s(t).attr("data-taxonomy");var a=s(t).find(".cs-type-tax").val(),i=[a];if("radio"!=a){var o=s(t).find(".term-alt-color").map(function(e,t){return s(t).val()}).get(),r=s(t).find(".nbtcs-term-image").map(function(e,t){return s(t).val()}).get();"image"==a?i.push({image:r}):i.push({color:o})}c.push(i)}),s.ajax({url:r,data:{action:"cs_save",product_id:l,type:a,tax:i,style:n,custom:c},type:"POST",datatype:"json",success:function(e){JSON.parse(e);d.unblock()},error:function(){alert("There was an error when processing data, please try again !"),d.unblock()}})}},d={block:function(){"undefined"!=typeof woocommerce_admin_meta_boxes_variations?s("#woocommerce-product-data").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}):s("#product_data_portlet .m-portlet__body").block({message:'<div class="loading-message"><div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>',overlayCSS:{background:"#555",opacity:.1}})},unblock:function(){if("undefined"!=typeof woocommerce_admin_meta_boxes_variations)var e=s("#woocommerce-product-data");else e=s("#product_data_portlet .m-portlet__body");e.unblock()}};c.init()}),jQuery(function(n){var c=function(e){e.block({message:'<div class="loading-message"><div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>',overlayCSS:{background:"#555",opacity:.1}})},s=function(e){e.unblock()};function o(e){e.find("tbody > tr").each(function(e){n(this).find(".pm-row-zero span").text(e+1)})}function r(e,t,a){return a.split(e).join(t)}n(document).on("click",".open-close select",function(){var e=n(this).val(),t=n(this).closest(".show-group-row").find(".shop-group-col.time");"open"==e?t.css("visibility","visible"):t.css("visibility","hidden")}),n(document).on("change","#data_country_enable",function(e){n(this).is(":checked")?n(".dokan-postcode").show():n(".dokan-postcode").hide()}),n(document).on("click",'[href="#add_shipping_method"]',function(e){e.preventDefault();var t=n(this).attr("data-id");n("#"+t).show(),n("#dokan-shipping").hide()}),n(document).on("click",'[href="#edit_zone"]',function(e){e.preventDefault();var t=n(this).attr("data-id");n("#shipping-"+t+"-method").show(),n("#dokan-shipping").hide()}),n(document).on("click",'[href="#dokan_shipping"]',function(){n("#dokan-shipping").show(),n(".add-shipping-method-wrapper").hide()}),n(document).on("click",'[href="#add-shipping-popup"]',function(e){e.preventDefault();var t=n(this).attr("data-id"),a=JSON.parse(n(this).attr("data-methods"));n.magnificPopup.open({items:{src:"#add-shipping-popup"},type:"inline",midClick:!0,mainClass:"mfp-fade",callbacks:{open:function(){n.each(a,function(e,t){n('#shipping_method option[value="'+t+'"]').attr("disabled","disabled")}),n(".btn-submit-shipping-add").attr("data-id",t)},close:function(){n("#shipping_method option").removeAttr("disabled")}}})}),n(document).on("click",".btn-submit-shipping-add",function(e){e.preventDefault(),c(n("#add-shipping-popup"));var t=n(this),a=n('[name="shipping_method"]').val(),i=t.attr("data-id");n.ajax({url:WooPanel.ajaxurl,data:{action:"woopanel_add_shipping_method",zoneID:i,method:a},type:"POST",datatype:"json",success:function(e){e.success&&location.reload()},error:function(){alert("There was an error when processing data, please try again !")}})}),n(document).on("change","#limit_zone",function(e){e.preventDefault(),n(this).is(":checked")?n(".dokan-postcode").show():n(".dokan-postcode").hide()}),n(document).on("click",".repeater-plus",function(e){e.preventDefault();var t=n(this).closest(".shipping_repeater"),a=n("#tmpl-shipping-repeater").html();a=r("{country}",t.find(".shipping-country-repeater").val(),a),t.find("tbody").append(a),o(t)}),n(document).on("click",".btn-add-location",function(e){e.preventDefault();var t=n("#tmpl-shipping-table").html();n(".woopanel-shipping-location-table").append(t)}),n(document).on("click",".shipping_repeater thead .repeater-minus",function(e){e.preventDefault(),n(this).closest(".shipping_repeater").remove()}),n(document).on("click",".repeater-minus",function(e){e.preventDefault();var t=n(this),a=t.closest(".shipping_repeater");t.closest(".pm-row").remove(),o(a)}),n(document).on("change",".shipping-country-repeater",function(e){e.preventDefault();var t=n(this),a=t.closest(".shipping_repeater"),i=n("#tmpl-shipping-repeater").html();i=r("{country}",t.val(),i),a.find("tbody").empty(),a.find("tbody").append(i),o(a)}),n(document).on("click",".shipping-method-delete",function(e){e.preventDefault(),c(n(".zone-wrapper"));var t=n(this),a=t.attr("data-zone"),i=t.attr("data-instance");n.ajax({url:WooPanel.ajaxurl,data:{action:"woopanel_delete_shipping_method",zoneID:a,instance_id:i},type:"POST",datatype:"json",success:function(e){e.success&&location.reload()},error:function(){alert("There was an error when processing data, please try again !")}})}),n(document).on("click",".shipping-method-edit",function(e){e.preventDefault();var t=n(this),a=t.attr("data-zone"),i=t.attr("data-instance"),o=t.attr("data-title"),r=t.attr("data-method");n.ajax({url:WooPanel.ajaxurl,data:{action:"woopanel_load_shipping_method",zoneID:a,instance_id:i},type:"POST",datatype:"json",success:function(e){e.success&&(n.each(e.data,function(e,t){n("#method_"+e).val(t)}),s(n("#edit-shipping-popup")))},error:function(){alert("There was an error when processing data, please try again !")}}),n.magnificPopup.open({items:{src:"#edit-shipping-popup"},type:"inline",midClick:!0,mainClass:"mfp-fade",callbacks:{open:function(){n("#edit-shipping-popup #instance_id").val(i),n("#edit-shipping-popup #method_id").val(r),n("#edit-shipping-popup #zoneID").val(a),n("#method_title").val(o),c(n("#edit-shipping-popup"))},close:function(){n(".blockUI").remove()}}})}),n(document).on("submit","#edit-shipping-form",function(e){e.preventDefault(),c(n("#edit-shipping-popup"));var t=n(this);n.ajax({url:WooPanel.ajaxurl,data:{action:"woopanel_edit_shipping_method",zoneID:t.find("#zoneID").val(),data:t.serialize()},type:"POST",datatype:"json",success:function(e){e.success&&location.reload()},error:function(){alert("There was an error when processing data, please try again !")}})})}),jQuery.fn.selectText=function(){var e=document,t=this[0];if(e.body.createTextRange)(a=document.body.createTextRange()).moveToElementText(t),a.select();else if(window.getSelection){var a,i=window.getSelection();(a=document.createRange()).selectNodeContents(t),i.removeAllRanges(),i.addRange(a)}},jQuery(function(c){if("undefined"!=typeof woocommerce_admin_meta_boxes_variations){var n=woocommerce_admin_meta_boxes_variations.ajax_url,t=woocommerce_admin_meta_boxes_variations.load_variations_nonce,a=nb_pricematrix.input_price_nonce,o=nb_pricematrix.save_price_nonce;woocommerce_admin_meta_boxes_variations.post_id}else if(0<c('[name="post_title"]').length&&"product"==c("#post_type").val())n=WooPanel.ajaxurl,t=WooPanel.product.load_variations_nonce,a=WooPanel.product.input_price_nonce,o=WooPanel.product.save_price_nonce,WooPanel.product.post_id;c("#woocommerce-product-data");var e={init:function(){c("li.price_matrix_tab a").on("click",this.initial_load),c(document).on("click",".pm-icon.-plus",this.add_row),c(document).on("click",".pm-icon.-minus",this.remove_row),c(document).on("focusout",".pm-attributes-field",function(){c(this).attr("data-option",this.value)}).on("change",".pm-attributes-field",this.change_attr),c(document).on("click",".save_price_matrix",this.save_price_matrix),c(document).on("change","#wc_price_matrix_is_heading",this.is_heading),c(document).on("click",".btn-enter-price",this.enter_price),c(document).on("click",".save_enter_price",this.save_price),c(document).on("change",".pm-direction-field",this.change_direction),c(document).on("change",".select-vacant-attribute",this.change_attr_enterprice),c(document).on("click","#_enable_price_matrix",this.enable_price_matrix),c(document).on("keyup",".entry-editing",this.tab_selected),c(document).on("click",".entry-editing",this.text_selected),c(document).on("click",".btn-order-attributes",this.show_order_attributes),c(document).on("change",".select-order-attribute",this.change_order_attribute),c(document).on("keydown",".entry-editing",this.nextTab),this.check_enable_price_matrix(),this.is_heading()},nextTab:function(e){if(9==event.which){var t=c(this),a=c(this).closest("td.price");if(c(".price-matrix-table td.price").removeClass("selected"),c(".price-matrix-table td.price .entry-editing").attr("contenteditable","false"),c(".price-matrix-table td.price .entry-editing").removeClass("entry-editing"),a.next().hasClass("price"))var i=a.next();else{var o=t.closest("tr").next();if(0<o.length)i=o.find("td.price").first()}void 0!==i&&(i.addClass("selected"),i.find(".wrap > div").addClass("entry-editing"),i.find(".wrap > div").attr("contenteditable","true"))}},initial_load:function(){s.block();var e=c("#post_ID").val();c.ajax({url:n,data:{action:"pricematrix_load_variations",security:t,product_id:e},type:"POST",datatype:"json",success:function(e){c(".woocommerce-message").remove(),null!=e.complete?c("#price_matrix_options_inner").html(e.template):c("#price_matrix_options_inner").html($html_msg),c("#price_matrix_table tbody").sortable({handle:".pm-handle",update:function(e,t){c("#price_matrix_table tbody > tr").each(function(e){c(this).find(".pm-handle span").text(e+1)})}}),c("#order_attributes tbody").each(function(e){c(this).sortable({handle:".pm-handle",update:function(e,t){var a=[],i=[],o=c(".select-order-attribute").val();c(this).find("> tr").each(function(e){var t=c(this).find(".pm-attributes-field");c(this).find(".pm-handle span").text(e+1),a.push(t.val()),i.push(t.val().trim())});var r=c("#post_ID").val();c.ajax({url:n,data:{action:"pricematrix_order_attribute",attribute:o,product_id:r,order_status:JSON.stringify(a),order_status_text:JSON.stringify(i)},type:"POST",datatype:"json",success:function(e){},error:function(){alert("There was an error when processing data, please try again !"),s.unblock()}})}})}),s.unblock()},error:function(){alert("There was an error when processing data, please try again !"),s.unblock()}})},show_order_attributes:function(e){e.preventDefault(),c("#order_attributes").slideToggle()},change_order_attribute:function(e){e.preventDefault();var t=c(this).val();c("#order_attributes table").hide(),c('#order_attributes table[data-id="'+t+'"]').show()},tab_selected:function(e){9===e.which&&c(this).selectText()},text_selected:function(){c(this).html()&&c(this).selectText()},check_enable_price_matrix:function(){c("#_enable_price_matrix").closest("label").hasClass("yes")?(c("#_enable_price_matrix").prop("checked",!0),c(".price_matrix_options").removeClass("hide")):(c("#_enable_price_matrix").prop("checked",!1),c(".price_matrix_options").addClass("hide"))},enable_price_matrix:function(){c(this).is(":checked")?(c(".price_matrix_options").removeClass("hide"),c(".m-tabs__item--active").removeClass("m-tabs__item--active")):(c(".price_matrix_options").addClass("hide"),c(".woocommerce_options_panel").hide(),c("#inventory_product_data").show(),c(".product_data_tabs > li").removeClass("active"),c(".inventory_options.inventory_tab").addClass("active"),c(".inventory_options.inventory_tab > a").addClass("m-tabs__item--active"),c("#price_matrix").removeClass("m-tabs-content__item--active"))},enter_price:function(){s.block();var e=c("#variable_product_options").find(".woocommerce_variations").data("attributes");c(this),c("select[name='pm_attr[]']").map(function(){return c(this).val()}).get(),c("select[name='pm_direction[]']").map(function(){return c(this).val()}).get();c("#price-matrix-popup").remove();var t=c("#post_ID").val();c.ajax({url:n,data:{action:"pricematrix_input_price",security:a,product_id:t,attr:e},type:"POST",datatype:"json",success:function(e){c(".woocommerce-message").remove(),null==e.complete?alert(e.msg):(c("body").append(e.html),c.magnificPopup.open({items:{src:"#price-matrix-popup"},type:"inline",midClick:!0,mainClass:"mfp-fade",callbacks:{open:function(){var e=c(window).width()-50,t=c(".price-matrix-table").width()+60;500<t&&t<e&&c("#price-matrix-popup").css({maxWidth:t})}}})),s.unblock()},error:function(){alert("There was an error when processing data, please try again !"),s.unblock()}})},save_price:function(){s.loading(),c(".save_enter_price").prop("disabled",!0);var a=[],i=[];c(".price-matrix-table td.price .wrap > div").each(function(e){var t=JSON.parse(c(this).closest("td.price").attr("data-attr"));a.push({price:c(this).text()}),i.push(t)}),c(".save_enter_price").text("Saving");var e=c("#post_ID").val();c.ajax({url:n,data:{action:"pricematrix_save_price",security:o,product_id:e,price:a,attr:i},type:"POST",datatype:"json",success:function(e){c(".woocommerce-message").remove(),null==e.complete?alert(e.msg):c(".save_enter_price").text("Saved"),s.unloaded(),c(".save_enter_price").prop("disabled",!1),s.unblock()},error:function(){alert("There was an error when processing data, please try again !"),s.unloaded()}})},is_heading:function(){var e=c("#wc_price_matrix_heading").closest("tr");c("#wc_price_matrix_is_heading").is(":checked")?e.show():e.hide()},add_row:function(){var e=parseInt(c("#price_matrix_table").attr("data-count")),t=c("#price_matrix_table tbody .pm-row").length,a=(c("#price_matrix_table").attr("data-product_variations"),[]),i=c(this).closest(".pm-row");return c("#price_matrix_table .pm-attributes-field").each(function(){a.push(c(this).val())}),e==t?alert("Exceeds max number of attributes limit."):(s.block(c("#price_matrix")),c.ajax({url:WooPanel.ajaxurl,data:{action:"pricematrix_add_row",security:c('[name="security"]').val(),product_id:WooPanel.product.post_id,attributes:a.toString()},type:"POST",datatype:"json",success:function(e){s.unblock(c("#price_matrix")),null!=e.complete&&c(e.template).insertAfter(i)},error:function(){alert("There was an error when processing data, please try again !"),s.unblock(c("#price_matrix"))}})),!1},remove_row:function(){if(2<c("#price_matrix_table tbody > tr").length){var e=c(this).closest(".pm-row").find(".pm-attributes-field");if(c("#price_matrix_table tbody > tr").each(function(e){c(this).find(".order span").text(e+1)}),!(t=c(".pm_repeater").attr("data-option")))var t=",";var a=t.split(","),i=a.indexOf(e.val());-1<i&&a.splice(i,1),a=a.filter(function(e){return""!=e.trim()}),html=a.join(),c(".pm_repeater").attr("data-option",html),c('.pm-attributes-field option[value="'+e.val()+'"]').removeAttr("disabled"),c(".btn-enter-price").prop("disabled",!0),c(".save_price_matrix").prop("disabled",!1),c(this).closest(".pm-row").remove()}else alert("Sorry, you can't remove this row, minimum requirement is 2 attributes!");return!1},change_direction:function(){c(".btn-enter-price").prop("disabled",!0),c(".save_price_matrix").prop("disabled",!1)},change_attr:function(e){if(c(".btn-enter-price").prop("disabled",!0),c(".save_price_matrix").prop("disabled",!1),c("body").removeAttr("data-msg"),e.length)var a=e;else{a=c(this);var t=c(".pm_repeater");if(!(i=t.attr("data-option")))var i=",";var o=i.split(",");if("0"!=a.val())o.push(a.val());else{var r=a.attr("data-option"),n=o.indexOf(r);-1<n&&o.splice(n,1)}o=o.filter(function(e){return""!=e.trim()}),html=o.join(),t.attr("data-option",html),c(".pm-attributes-field option").each(function(e){if(0==a.val())r==c(this).attr("value")&&c(this).removeAttr("disabled");else for(var t=0;t<o.length;++t)c(this).attr("value")!=c(this).closest("select").val()&&o[t]==c(this).attr("value")&&c(this).attr("disabled","disabled")})}},change_attr_enterprice:function(){c("#price-matrix-popup").block({message:'<div class="loading-message"><div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>',overlayCSS:{background:"#555",opacity:.1}});c(this).val();var i={};c(".select-vacant-attribute").each(function(e){if(c(this).val()){var t=c(this).attr("id"),a=c(this).val();i[t]=a}}),c.ajax({url:WooPanel.ajaxurl,data:{action:"pricematrix_load_table",security:c('[name="security"]').val(),product_id:WooPanel.product.post_id,vacant:i,load:!0},type:"POST",datatype:"json",success:function(e){c(".table-responsive").html(e.template),c("#price-matrix-popup").unblock()},error:function(){alert("There was an error when processing data, please try again !"),c("#price-matrix-popup").unblock()}})},save_price_matrix:function(){var t=c(this),e=c("select[name='pm_attr[]']").map(function(){return c(this).val()}).get(),a=c("select[name='pm_direction[]']").map(function(){return c(this).val()}).get();s.block(),c(".woocommerce-message.msg-enter-price").remove();var i=c("#post_ID").val();c.ajax({url:n,data:{action:"pm_save_variations",security:c('[name="security"]').val(),product_id:i,pm_attr:e,pm_direction:a,show:c('[name="_pm_show_on"]').val()},type:"POST",datatype:"json",success:function(e){null!=e.complete?(c("#price_matrix_options_inner").append(e.notice),t.prop("disabled",!0),c(".btn-enter-price").removeAttr("disabled")):alert(e.message),s.unblock()},error:function(){alert("There was an error when processing data, please try again !"),s.unblock()}})}},s={block:function(){"undefined"!=typeof woocommerce_admin_meta_boxes_variations?c("#woocommerce-product-data").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}):c("#product_data_portlet .m-portlet__body").block({message:'<div class="loading-message"><div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>',overlayCSS:{background:"#555",opacity:.1}})},unblock:function(){if("undefined"!=typeof woocommerce_admin_meta_boxes_variations)var e=c("#woocommerce-product-data");else e=c("#product_data_portlet .m-portlet__body");e.unblock()},loading:function(){c("#price-matrix-popup").block({message:null,overlayCSS:{background:"#fff",opacity:.6}})},unloaded:function(){c("#price-matrix-popup").unblock()},log:function(e){c("#log").append('<p style="margin: 0;padding: 0;">- '+e+"</p>")},option:function(e){c("#log-cha span").html(e)}},i=".price-matrix-table td.price",r={init:function(){c(document).on("click",i,this.live_selected),c(document).on("dblclick",i,this.input_data)},live_selected:function(){var e=c(i).not(this).find(".wrap > div");e.removeClass("entry-editing"),e.attr("contenteditable",!1),c(i).removeClass("selected"),c(this).addClass("selected");c(this).index()},input_data:function(){var e=c(this).find(".wrap > div");e.addClass("entry-editing"),e.attr("contenteditable",!0),e.trigger("focus")}};e.init(),r.init()});