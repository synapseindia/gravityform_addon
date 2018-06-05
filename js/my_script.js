// placeholder for javascript
jQuery('document').ready(function(e){
	jQuery('body').on('click','.gf_delete_field_choice',function(){
		jQuery(this).parent().remove();
	});
	jQuery('body').on('click','.gf_insert_field_choice',function(){
		jQuery(this).parent().append(notification_conditionhtml);
	});
});