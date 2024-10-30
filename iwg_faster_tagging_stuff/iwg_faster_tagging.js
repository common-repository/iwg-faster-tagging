/*
	This file is part of the "iwg faster tagging" plugin for wordpress
	Copyright (C) 2008 Thomas Schneider
	http://www.im-web-gefunden.de/
 */
function iwg_addTag(clickedTag){
	var current_tags = jQuery.trim(jQuery('#tags-input').val());
	current_tags = current_tags.replace(/(\s{0,},\s{0,})/g, ',');
	var re = new RegExp(clickedTag);
	if (!current_tags.match(re)) {
		if (current_tags.length > 0) {
			current_tags += ",";
		}
		current_tags += clickedTag;
		current_tags = current_tags.replace(/(\s{0,},\s{0,})/g, ', ');
		jQuery('#tags-input').val(current_tags);
	}
	iwg_tag_update_quickclicks();
	return false;
};

function iwg_tag_update_quickclicks(){
	var current_tags = jQuery.trim(jQuery('#tags-input').val());
	current_tags = current_tags.replace(/(\s{0,},\s{0,})/g, ',');
	var current_tags_arr = current_tags.split(',');
	current_tags_arr = current_tags_arr.sort(function(a, b){
		a = jQuery.trim(a.toUpperCase());
		b = jQuery.trim(b.toUpperCase());
		return (a == b) ? 0 : (a > b) ? 1 : -1;
	});
	jQuery('#tagchecklist').empty();
	shown = false;
	jQuery.each(current_tags_arr, function(key, val){
		val = jQuery.trim(val);
		if (!val.match(/^\s+$/) && '' != val) {
			txt = '<span><a id="tag-check-' + key + '" class="ntdelbutton">X</a>&nbsp;' + val + '</span> ';
			jQuery('#tagchecklist').append(txt);
			jQuery('#tag-check-' + key).click(new_tag_remove_tag);
			shown = true;
		}
	});
	if (shown) {
		jQuery('#tagchecklist').prepend('<strong>' + postL10n.tagsUsed + '</strong><br />');
		current_tags = jQuery.trim(current_tags_arr.join(','));
		current_tags = current_tags.replace(/(\s{0,},\s{0,})/g, ', ');
		jQuery('#tags-input').val(current_tags);
	}
		return false;
}